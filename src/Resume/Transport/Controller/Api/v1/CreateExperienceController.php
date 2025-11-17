<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Formation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
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
class CreateExperienceController extends AbstractController
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
     * @throws \Exception
     */
    #[Route(
        path: '/v1/resume/experience',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        HubInterface $hub
    ): JsonResponse {
        $experience = new Experience();
        $experience->setTitle($request->request->get('title'));
        $experience->setCompany($request->request->get('company'));
        $experience->setDescription($request->request->get('description'));
        $experience->setUser(Uuid::fromString($loggedInUser->getId()));
        $experience->setStartedAt(new DateTimeImmutable($request->request->get('startedAt')));
        $experience->setEndedAt(new DateTimeImmutable($request->request->get('endedAt')));

        $this->entityManager->persist($experience);
        $this->entityManager->flush();


        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $experience,
                'json',
                [ 'groups' => 'Experience',]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
