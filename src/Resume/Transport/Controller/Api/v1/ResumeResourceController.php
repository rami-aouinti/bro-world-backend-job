<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Application\Messenger\Command\CreateResumeEntityCommand;
use App\Resume\Application\Messenger\Command\DeleteResumeEntityCommand;
use App\Resume\Application\Messenger\Command\UpdateResumeEntityCommand;
use App\Resume\Application\Service\ResumeCacheKeyGenerator;
use App\Resume\Application\Service\ResumeEntityDefinition;
use App\Resume\Application\Service\ResumeEntityRegistry;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use Doctrine\Persistence\ManagerRegistry;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use function array_map;
use function sprintf;

#[AsController]
#[OA\Tag(name: 'Resume')]
class ResumeResourceController extends AbstractController
{
    private const CACHE_TTL = 300;

    private const RESOURCE_LABELS = [
        ResumeEntityRegistry::RESOURCE_SKILLS => 'Skill',
        ResumeEntityRegistry::RESOURCE_LANGUAGES => 'Language',
        ResumeEntityRegistry::RESOURCE_HOBBIES => 'Hobby',
        ResumeEntityRegistry::RESOURCE_EXPERIENCES => 'Experience',
        ResumeEntityRegistry::RESOURCE_EDUCATIONS => 'Education',
        ResumeEntityRegistry::RESOURCE_REFERENCES => 'Reference',
        ResumeEntityRegistry::RESOURCE_PROJECTS => 'Project',
    ];

    public function __construct(
        private readonly ResumeEntityRegistry $registry,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TagAwareCacheInterface $cache,
        private readonly SerializerInterface $serializer,
        #[Autowire(service: 'messenger.bus.command')]
        private readonly MessageBusInterface $commandBus,
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(
        path: '/v1/resume/{resource}',
        name: 'resume_resource_create',
        requirements: ['resource' => ResumeEntityRegistry::RESOURCE_REQUIREMENT],
        methods: [Request::METHOD_POST],
    )]
    public function create(
        SymfonyUser $user,
        Request $request,
        string $resource
    ): JsonResponse {
        $definition = $this->registry->getDefinition($resource);

        try {
            $payload = $this->decodePayload($request);
        } catch (JsonException $exception) {
            return $this->buildJsonResponse('Invalid JSON payload', [], Response::HTTP_BAD_REQUEST);
        }
        $userId = $definition->scopedByUser ? $user->getId() : null;

        try {
            $entity = $this->dispatchCommand(new CreateResumeEntityCommand($resource, $userId, $payload));
        } catch (ValidationFailedException $exception) {
            return $this->buildJsonResponse(
                'Invalid input',
                $this->formatViolations($exception->getViolations()),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), [], Response::HTTP_BAD_REQUEST);
        }

        return $this->buildJsonResponse(
            sprintf('%s created', $this->humanizeResource($resource)),
            $this->serializeEntity($entity, $definition),
            Response::HTTP_CREATED
        );
    }

    #[Route(
        path: '/v1/resume/{resource}/{id}',
        name: 'resume_resource_get',
        requirements: ['resource' => ResumeEntityRegistry::RESOURCE_REQUIREMENT],
        methods: [Request::METHOD_GET],
    )]
    public function getItem(
        SymfonyUser $user,
        string $resource,
        string $id
    ): JsonResponse {
        $definition = $this->registry->getDefinition($resource);
        $userId = $definition->scopedByUser ? $user->getId() : null;

        try {
            $data = $this->cache->get(
                ResumeCacheKeyGenerator::entityItemKey($resource, $id, $userId),
                function (ItemInterface $item) use ($definition, $resource, $id, $userId) {
                    $item->expiresAfter(self::CACHE_TTL);
                    $item->tag(ResumeCacheKeyGenerator::entityItemTag($resource, $id, $userId));

                    $entity = $this->findEntity($definition, $id, $userId);

                    return $this->serializeEntity($entity, $definition);
                }
            );
        } catch (NotFoundHttpException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), [], Response::HTTP_NOT_FOUND);
        }

        return $this->buildJsonResponse(
            sprintf('%s details', $this->humanizeResource($resource)),
            $data
        );
    }

    #[Route(
        path: '/v1/resume/{resource}',
        name: 'resume_resource_list',
        requirements: ['resource' => ResumeEntityRegistry::RESOURCE_REQUIREMENT],
        methods: [Request::METHOD_GET],
    )]
    public function list(
        SymfonyUser $user,
        string $resource
    ): JsonResponse {
        $definition = $this->registry->getDefinition($resource);
        $userId = $definition->scopedByUser ? $user->getId() : null;

        $data = $this->cache->get(
            ResumeCacheKeyGenerator::entityListKey($resource, $userId),
            function (ItemInterface $item) use ($definition, $resource, $userId) {
                $item->expiresAfter(self::CACHE_TTL);
                $item->tag(ResumeCacheKeyGenerator::entityListTag($resource, $userId));

                $repository = $this->managerRegistry->getRepository($definition->entityClass);
                $criteria = [];

                if ($definition->scopedByUser && $userId !== null) {
                    $criteria['user'] = Uuid::fromString($userId);
                }

                $result = $repository->findBy($criteria);

                return array_map(
                    fn (object $entity) => $this->serializeEntity($entity, $definition),
                    $result
                );
            }
        );

        return $this->buildJsonResponse(
            sprintf('%s list', $this->humanizeResource($resource)),
            $data
        );
    }

    /**
     * @throws JsonException
     */
    #[Route(
        path: '/v1/resume/{resource}/{id}',
        name: 'resume_resource_update',
        requirements: ['resource' => ResumeEntityRegistry::RESOURCE_REQUIREMENT],
        methods: [Request::METHOD_PUT],
    )]
    public function update(
        SymfonyUser $user,
        Request $request,
        string $resource,
        string $id
    ): JsonResponse {
        $definition = $this->registry->getDefinition($resource);

        try {
            $payload = $this->decodePayload($request);
        } catch (JsonException $exception) {
            return $this->buildJsonResponse('Invalid JSON payload', [], Response::HTTP_BAD_REQUEST);
        }
        $userId = $definition->scopedByUser ? $user->getId() : null;

        try {
            $entity = $this->dispatchCommand(new UpdateResumeEntityCommand($resource, $id, $userId, $payload));
        } catch (ValidationFailedException $exception) {
            return $this->buildJsonResponse(
                'Invalid input',
                $this->formatViolations($exception->getViolations()),
                Response::HTTP_BAD_REQUEST
            );
        } catch (NotFoundHttpException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), [], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), [], Response::HTTP_BAD_REQUEST);
        }

        return $this->buildJsonResponse(
            sprintf('%s updated', $this->humanizeResource($resource)),
            $this->serializeEntity($entity, $definition)
        );
    }

    #[Route(
        path: '/v1/resume/{resource}/{id}',
        name: 'resume_resource_delete',
        requirements: ['resource' => ResumeEntityRegistry::RESOURCE_REQUIREMENT],
        methods: [Request::METHOD_DELETE],
    )]
    public function delete(
        SymfonyUser $user,
        string $resource,
        string $id
    ): JsonResponse {
        $definition = $this->registry->getDefinition($resource);
        $userId = $definition->scopedByUser ? $user->getId() : null;

        try {
            $entity = $this->dispatchCommand(new DeleteResumeEntityCommand($resource, $id, $userId));
        } catch (NotFoundHttpException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), [], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $exception) {
            return $this->buildJsonResponse($exception->getMessage(), [], Response::HTTP_BAD_REQUEST);
        }

        return $this->buildJsonResponse(
            sprintf('%s deleted', $this->humanizeResource($resource)),
            $this->serializeEntity($entity, $definition)
        );
    }

    private function serializeEntity(object $entity, ResumeEntityDefinition $definition): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->serializer->normalize($entity, null, ['groups' => $definition->serializationGroup]);

        return $data;
    }

    private function dispatchCommand(object $command): mixed
    {
        $envelope = $this->commandBus->dispatch($command);
        /** @var HandledStamp|null $handled */
        $handled = $envelope->last(HandledStamp::class);

        return $handled?->getResult();
    }

    private function humanizeResource(string $resource): string
    {
        return self::RESOURCE_LABELS[$resource] ?? ucfirst($resource);
    }

    private function buildJsonResponse(string $message, array $data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return $this->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errorData = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errorData;
    }

    /**
     * @throws JsonException
     */
    private function decodePayload(Request $request): array
    {
        $content = $request->getContent();

        if ($content === null || $content === '') {
            return [];
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $payload;
    }

    private function findEntity(ResumeEntityDefinition $definition, string $id, ?string $userId): object
    {
        $repository = $this->managerRegistry->getRepository($definition->entityClass);
        $criteria = ['id' => Uuid::fromString($id)];

        if ($definition->scopedByUser) {
            if ($userId === null) {
                throw new NotFoundHttpException('User context missing.');
            }
            $criteria['user'] = Uuid::fromString($userId);
        }

        $entity = $repository->findOneBy($criteria);

        if ($entity === null) {
            throw new NotFoundHttpException(sprintf('Resource %s with id %s not found.', $definition->resource, $id));
        }

        return $entity;
    }
}
