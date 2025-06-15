<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\General\Domain\Utils\JSON;
use App\Notification\Application\Service\NotificationService;
use App\Notification\Domain\Entity\Notification;
use App\Resume\Domain\Entity\Media;
use App\Resume\Domain\Entity\Project;
use App\Resume\Domain\Entity\Reference;
use App\Resume\Domain\Entity\Skill;
use App\Resume\Infrastructure\Repository\SkillRepository;
use App\User\Domain\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationService $notificationService
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws \Exception
     */
    #[Route(
        path: '/v1/resume/reference',
        methods: [Request::METHOD_POST],
    )]
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[OA\Response(
        response: 200,
        description: 'User profile data',
        content: new JsonContent(
            ref: new Model(
                type: Reference::class,
                groups: [Reference::SET_USER_REFERENCE],
            ),
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid token (not found or expired)',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 401,
                'message' => 'JWT Token not found',
            ],
        ),
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 403,
                'message' => 'Access denied',
            ],
        ),
    )]
    public function __invoke(
        User $loggedInUser,
        Request $request,
        SkillRepository $skillRepository,
        HubInterface $hub
    ): JsonResponse {
        $notification = new Notification();
        $notification->setUser($loggedInUser);
        $notification->setMessage('New reference was added');
        $notification->setIsRead(false);

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
        $reference->setUser($loggedInUser);

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
        $this->entityManager->persist($notification);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $this->notificationService->sendNotification($loggedInUser, $notification);

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
