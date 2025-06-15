<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\General\Domain\Utils\JSON;
use App\Notification\Application\Service\NotificationService;
use App\Notification\Domain\Entity\Notification;
use App\Resume\Domain\Entity\Skill;
use App\Resume\Infrastructure\Repository\SkillRepository;
use App\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
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
class UpdateSkillController extends AbstractController
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
     */
    #[Route(
        path: '/v1/resume/skill/{skill}',
        methods: [Request::METHOD_PATCH],
    )]
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[OA\Response(
        response: 200,
        description: 'User profile data',
        content: new JsonContent(
            ref: new Model(
                type: Skill::class,
                groups: [Skill::SET_USER_SKILL],
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
        Skill $skill,
        HubInterface $hub
    ): JsonResponse {

        $notification = new Notification();
        $notification->setUser($loggedInUser);
        $notification->setMessage('New Skill was updated');
        $notification->setIsRead(false);

        $skillEntity = $skillRepository->find($skill);

        if($request->request->get('name')) {
            $skillEntity->setName($request->request->get('name'));
        }
        if($request->request->get('type')) {
            $skillEntity->setType($request->request->get('type'));

        }
        if($request->request->get('level')) {
            $skillEntity->setLevel((int)$request->request->get('level'));
        }

        $this->entityManager->persist($skillEntity);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $this->notificationService->sendNotification($loggedInUser, $notification);

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                'skill updated',
                'json',
                []
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
