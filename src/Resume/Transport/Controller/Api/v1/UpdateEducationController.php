<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Formation;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[OA\Tag(name: 'Resume')]
class UpdateEducationController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/education/{education}', methods: [Request::METHOD_PATCH])]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        Formation $education
    ): JsonResponse {
        if ($education->getUser()->toString() !== $loggedInUser->getId()) {
            throw new AccessDeniedHttpException('You cannot edit this education.');
        }

        if (($name = $request->request->get('name')) !== null) {
            $education->setName($name);
        }

        if (($school = $request->request->get('school')) !== null) {
            $education->setSchool($school);
        }

        if (($gradeLevel = $request->request->get('gradeLevel')) !== null) {
            $education->setGradeLevel((int) $gradeLevel);
        }

        if (($description = $request->request->get('description')) !== null) {
            $education->setDescription($description);
        }

        if (($startedAt = $request->request->get('startedAt')) !== null) {
            $education->setStartedAt(new DateTimeImmutable($startedAt));
        }

        if (($endedAt = $request->request->get('endedAt')) !== null) {
            $education->setEndedAt($endedAt === '' ? null : new DateTimeImmutable($endedAt));
        }

        $this->entityManager->flush();

        /** @var array<string, mixed> $output */
        $output = JSON::decode(
            $this->serializer->serialize($education, 'json', ['groups' => 'Formation']),
            true,
        );

        return new JsonResponse($output);
    }
}
