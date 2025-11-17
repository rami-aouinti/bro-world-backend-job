<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Experience;
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
class UpdateExperienceController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/experience/{experience}', methods: [Request::METHOD_PATCH])]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        Experience $experience
    ): JsonResponse {
        if ($experience->getUser()->toString() !== $loggedInUser->getId()) {
            throw new AccessDeniedHttpException('You cannot edit this experience.');
        }

        if (($title = $request->request->get('title')) !== null) {
            $experience->setTitle($title);
        }

        if (($description = $request->request->get('description')) !== null) {
            $experience->setDescription($description);
        }

        if (($company = $request->request->get('company')) !== null) {
            $experience->setCompany($company);
        }

        if (($startedAt = $request->request->get('startedAt')) !== null) {
            $experience->setStartedAt(new DateTimeImmutable($startedAt));
        }

        if (($endedAt = $request->request->get('endedAt')) !== null) {
            $experience->setEndedAt($endedAt === '' ? null : new DateTimeImmutable($endedAt));
        }

        $this->entityManager->flush();

        /** @var array<string, mixed> $output */
        $output = JSON::decode(
            $this->serializer->serialize($experience, 'json', ['groups' => 'Experience']),
            true,
        );

        return new JsonResponse($output);
    }
}
