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
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Resume
 */
#[AsController]
#[OA\Tag(name: 'Resume')]
readonly class CreateEducationController
{
    public function __construct(
        private SerializerInterface    $serializer,
        private EntityManagerInterface $entityManager,
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
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request
    ): JsonResponse {

        $education = new Formation();
        $education->setName($request->request->get('name'));
        $education->setDescription($request->request->get('description'));
        $education->setSchool($request->request->get('school'));
        $education->setGradeLevel((int)$request->request->get('gradeLevel'));
        $education->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
        $education->setStartedAt(new DateTimeImmutable('now'));
        $education->setEndedAt(new DateTimeImmutable('now'));

        $this->entityManager->persist($education);
        $this->entityManager->flush();


        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $education,
                'json',
                [ 'groups' => 'Formation',]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
