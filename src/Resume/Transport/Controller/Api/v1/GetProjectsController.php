<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Infrastructure\Repository\ProjectRepository;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[OA\Tag(name: 'Resume')]
class GetProjectsController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ProjectRepository $projectRepository
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/project', methods: [Request::METHOD_GET])]
    public function __invoke(): JsonResponse
    {
        $projects = $this->projectRepository->findAll();

        /** @var array<array<string, mixed>> $output */
        $output = JSON::decode(
            $this->serializer->serialize($projects, 'json', ['groups' => 'Project']),
            true,
        );

        return new JsonResponse($output);
    }
}
