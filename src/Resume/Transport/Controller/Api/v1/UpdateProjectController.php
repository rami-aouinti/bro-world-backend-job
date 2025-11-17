<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Project;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[OA\Tag(name: 'Resume')]
class UpdateProjectController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/project/{project}', methods: [Request::METHOD_PATCH])]
    public function __invoke(Request $request, Project $project): JsonResponse
    {
        if (($name = $request->request->get('name')) !== null) {
            $project->setName($name);
        }

        if (($description = $request->request->get('description')) !== null) {
            $project->setDescription($description);
        }

        if (($gitLink = $request->request->get('gitLink')) !== null) {
            $project->setGitLink($gitLink === '' ? null : $gitLink);
        }

        $this->entityManager->flush();

        /** @var array<string, mixed> $output */
        $output = JSON::decode(
            $this->serializer->serialize($project, 'json', ['groups' => 'Project']),
            true,
        );

        return new JsonResponse($output);
    }
}
