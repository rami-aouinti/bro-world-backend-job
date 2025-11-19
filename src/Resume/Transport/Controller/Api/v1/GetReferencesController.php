<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Infrastructure\Repository\ReferenceRepository;
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
class GetReferencesController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ReferenceRepository $referenceRepository
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/reference', methods: [Request::METHOD_GET])]
    public function __invoke(SymfonyUser $loggedInUser): JsonResponse
    {
        $references = $this->referenceRepository->findBy([
            'user' => Uuid::fromString($loggedInUser->getId()),
        ]);

        /** @var array<array<string, mixed>> $output */
        $output = JSON::decode(
            $this->serializer->serialize($references, 'json', ['groups' => 'Reference']),
            true,
        );

        return new JsonResponse($output);
    }
}
