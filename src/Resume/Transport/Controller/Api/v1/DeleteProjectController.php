<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'Resume')]
class DeleteProjectController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/v1/resume/project/{project}', methods: [Request::METHOD_DELETE])]
    public function __invoke(Project $project): JsonResponse
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'project deleted']);
    }
}
