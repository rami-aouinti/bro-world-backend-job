<?php

declare(strict_types=1);

namespace App\Resume\Application\Service;

use App\Resume\Domain\Entity\Media;
use App\Resume\Domain\Entity\Reference;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use function array_key_exists;
use function is_array;

final class ResumeEntityManager
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        private readonly ResumeEntityRegistry $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly TagAwareCacheInterface $cache
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function create(string $resource, ?string $userId, array $payload): object
    {
        $definition = $this->registry->getDefinition($resource);
        $entityClass = $definition->entityClass;
        $entity = new $entityClass();

        $userUuid = $this->resolveUserId($definition, $userId);

        if ($userUuid !== null && method_exists($entity, 'setUser')) {
            $entity->setUser($userUuid);
        }

        $this->applyPayload($definition, $entity, $payload);
        $this->validate($entity);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->cache->invalidateTags([
            ResumeCacheKeyGenerator::entityListTag($resource, $userId),
        ]);

        return $entity;
    }

    public function update(string $resource, string $entityId, ?string $userId, array $payload): object
    {
        $definition = $this->registry->getDefinition($resource);
        $entity = $this->findEntity($definition, $entityId, $userId);

        $this->applyPayload($definition, $entity, $payload);
        $this->validate($entity);

        $this->entityManager->flush();

        $this->cache->deleteItem(ResumeCacheKeyGenerator::entityItemKey($resource, $entityId, $userId));
        $this->cache->invalidateTags([
            ResumeCacheKeyGenerator::entityListTag($resource, $userId),
            ResumeCacheKeyGenerator::entityItemTag($resource, $entityId, $userId),
        ]);

        return $entity;
    }

    public function delete(string $resource, string $entityId, ?string $userId): object
    {
        $definition = $this->registry->getDefinition($resource);
        $entity = $this->findEntity($definition, $entityId, $userId);

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $this->cache->deleteItem(ResumeCacheKeyGenerator::entityItemKey($resource, $entityId, $userId));
        $this->cache->invalidateTags([
            ResumeCacheKeyGenerator::entityListTag($resource, $userId),
            ResumeCacheKeyGenerator::entityItemTag($resource, $entityId, $userId),
        ]);

        return $entity;
    }

    private function findEntity(ResumeEntityDefinition $definition, string $entityId, ?string $userId): object
    {
        $repository = $this->entityManager->getRepository($definition->entityClass);
        $criteria = ['id' => Uuid::fromString($entityId)];

        if ($definition->scopedByUser) {
            $criteria['user'] = $this->resolveUserId($definition, $userId);
        }

        $entity = $repository->findOneBy($criteria);

        if ($entity === null) {
            throw new NotFoundHttpException(sprintf('Resource %s with id %s not found.', $definition->resource, $entityId));
        }

        return $entity;
    }

    private function applyPayload(ResumeEntityDefinition $definition, object $entity, array $payload): void
    {
        foreach ($definition->fields as $field => $config) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $value = $payload[$field];
            $nullable = $config['nullable'] ?? false;

            switch ($config['type']) {
                case ResumeEntityDefinition::TYPE_INT:
                    $this->propertyAccessor->setValue($entity, $field, $value === null ? null : (int) $value);
                    break;
                case ResumeEntityDefinition::TYPE_DATE:
                    $this->propertyAccessor->setValue($entity, $field, $this->buildDate($value, $nullable));
                    break;
                case ResumeEntityDefinition::TYPE_MEDIA_COLLECTION:
                    $this->syncMediaCollection($entity, is_array($value) ? $value : []);
                    break;
                default:
                    $this->propertyAccessor->setValue($entity, $field, $value);
            }
        }
    }

    private function buildDate(mixed $value, bool $nullable): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            if ($nullable) {
                return null;
            }

            throw new InvalidArgumentException('Date value cannot be null.');
        }

        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        try {
            return new DateTimeImmutable((string) $value);
        } catch (\Exception $exception) {
            throw new InvalidArgumentException('Invalid date value provided.', $exception->getCode(), $exception);
        }
    }

    private function syncMediaCollection(object $entity, array $medias): void
    {
        if (!$entity instanceof Reference) {
            throw new InvalidArgumentException('Media collections are only supported for references.');
        }

        foreach ($entity->getMedias() as $media) {
            $entity->removeMedia($media);
            $this->entityManager->remove($media);
        }

        foreach ($medias as $mediaData) {
            if (!is_array($mediaData) || empty($mediaData['path'])) {
                continue;
            }

            $media = new Media();
            $media->setPath((string) $mediaData['path']);
            $entity->addMedia($media);
            $this->entityManager->persist($media);
        }
    }

    private function validate(object $entity): void
    {
        $violations = $this->validator->validate($entity);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }

    private function resolveUserId(ResumeEntityDefinition $definition, ?string $userId): ?UuidInterface
    {
        if (!$definition->scopedByUser) {
            return null;
        }

        if ($userId === null) {
            throw new InvalidArgumentException(sprintf('Resource %s requires a user context.', $definition->resource));
        }

        return Uuid::fromString($userId);
    }
}
