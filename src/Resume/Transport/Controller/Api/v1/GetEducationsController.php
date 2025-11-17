<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Infrastructure\Repository\FormationRepository;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[OA\Tag(name: 'Resume')]
class GetEducationsController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly FormationRepository $formationRepository
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/education', methods: [Request::METHOD_GET])]
    public function __invoke(SymfonyUser $loggedInUser): JsonResponse
    {
        $educations = $this->formationRepository->findBy([
            'user' => Uuid::fromString($loggedInUser->getId()),
        ]);

        /** @var array<array<string, mixed>> $output */
        $output = JSON::decode(
            $this->serializer->serialize($educations, 'json', ['groups' => 'Formation']),
            true,
        );

        return new JsonResponse($output);
    }
}
