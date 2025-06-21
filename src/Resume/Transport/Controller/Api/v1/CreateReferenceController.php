<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Media;
use App\Resume\Domain\Entity\Project;
use App\Resume\Domain\Entity\Reference;
use App\Resume\Infrastructure\Repository\SkillRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Resume
 */
#[AsController]
#[OA\Tag(name: 'Resume')]
class CreateReferenceController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws Exception
     */
    #[Route(
        path: '/v1/resume/reference',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        SkillRepository $skillRepository,
        HubInterface $hub
    ): JsonResponse {

        $project = new Project();
        $project->setName($request->request->get('projectName'));
        $project->setDescription($request->request->get('projectDescription'));
        $project->setGitLink($request->request->get('projectGithubLink'));


        $reference = new Reference();
        $reference->setTitle($request->request->get('referenceTitle'));
        $reference->setDescription($request->request->get('referenceDescription'));
        $reference->setCompany($request->request->get('referenceCompany'));
        $reference->setStartedAt(new DateTimeImmutable($request->request->get('referenceStartedAt')));
        $reference->setEndedAt(new DateTimeImmutable($request->request->get('referenceEndedAt')));
        $reference->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));

        $files = $request->files->get('photo');

        if (!$files) {
            return new JsonResponse([
                'error' => 'No file uploaded',
            ], 400);
        }
        $uploadDir = $this->getParameter('uploads_directory');
        foreach ($files as $file){

            $media = new Media();
            $media->setPath('file_name');
            $media->setFile($file);
            $reference->addMedia($media);
            $this->entityManager->persist($media);
        }



            $skill = $skillRepository->findOneBy([
                'name' => $request->request->get('skillName'),
                'user' => $loggedInUser
            ]);
            if (!$skill) {
                //$skill = new Skill();
                //$skill->setUser($loggedInUser);
                //$skill->setName($skillsArray['name']);
                //$skill->setType($skillsArray['type']);
                //$skill->setLevel((int)$skillsArray['level']);
                //$reference->addProject($project);
            }
            $project->addSkill($skill);


        $reference->addProject($project);


        $this->entityManager->persist($project);

        $this->entityManager->flush();


        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                'reference created',
                'json',
                []
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
