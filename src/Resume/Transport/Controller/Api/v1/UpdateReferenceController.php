<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Reference;
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
class UpdateReferenceController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/reference/{reference}', methods: [Request::METHOD_PATCH])]
    public function __invoke(SymfonyUser $loggedInUser, Request $request, Reference $reference): JsonResponse
    {
        if ($reference->getUser()->toString() !== $loggedInUser->getId()) {
            throw new AccessDeniedHttpException('You cannot edit this reference.');
        }

        if (($title = $request->request->get('title')) !== null) {
            $reference->setTitle($title);
        }

        if (($company = $request->request->get('company')) !== null) {
            $reference->setCompany($company);
        }

        if (($description = $request->request->get('description')) !== null) {
            $reference->setDescription($description);
        }

        if (($startedAt = $request->request->get('startedAt')) !== null) {
            $reference->setStartedAt(new DateTimeImmutable($startedAt));
        }

        if (($endedAt = $request->request->get('endedAt')) !== null) {
            $reference->setEndedAt($endedAt === '' ? null : new DateTimeImmutable($endedAt));
        }

        $this->entityManager->flush();

        /** @var array<string, mixed> $output */
        $output = JSON::decode(
            $this->serializer->serialize($reference, 'json', ['groups' => 'Reference']),
            true,
        );

        return new JsonResponse($output);
    }
}
