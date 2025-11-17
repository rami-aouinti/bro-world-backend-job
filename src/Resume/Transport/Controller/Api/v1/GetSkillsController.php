<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Infrastructure\Repository\SkillRepository;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[OA\Tag(name: 'Resume')]
class GetSkillsController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly SkillRepository $skillRepository
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/skill', methods: [Request::METHOD_GET])]
    public function __invoke(SymfonyUser $loggedInUser): JsonResponse
    {
        $skills = $this->skillRepository->findBy([
            'user' => Uuid::fromString($loggedInUser->getId()),
        ]);

        /** @var array<array<string, mixed>> $output */
        $output = JSON::decode(
            $this->serializer->serialize($skills, 'json', ['groups' => 'Skill']),
            true,
        );

        return new JsonResponse($output);
    }
}
