<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Skill;
use App\Resume\Infrastructure\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
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
        private readonly EntityManagerInterface $entityManager
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
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        SkillRepository $skillRepository,
        Skill $skill,
        HubInterface $hub
    ): JsonResponse {

        $skillEntity = $skillRepository->find($skill);

        if($request->request->get('name')) {
            $skillEntity?->setName($request->request->get('name'));
        }
        if($request->request->get('type')) {
            $skillEntity?->setType($request->request->get('type'));

        }
        if($request->request->get('level')) {
            $skillEntity?->setLevel((int)$request->request->get('level'));
        }

        $this->entityManager->persist($skillEntity);
        $this->entityManager->flush();


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
