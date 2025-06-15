<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Formation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
class CreateEducationController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws Exception
     */
    #[Route(
        path: '/v1/resume/education',
        methods: [Request::METHOD_POST],
    )]
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        HubInterface $hub
    ): JsonResponse {

        $education = new Formation();
        $education->setName($request->request->get('name'));
        $education->setDescription($request->request->get('description'));
        $education->setSchool($request->request->get('school'));
        $education->setGradeLevel((int)$request->request->get('gradeLevel'));
        $education->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
        $education->setStartedAt(new DateTimeImmutable($request->request->get('startedAt')));
        $education->setEndedAt(new DateTimeImmutable($request->request->get('endedAt')));

        $this->entityManager->persist($education);
        $this->entityManager->flush();


        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                'notification created',
                'json',
                []
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
