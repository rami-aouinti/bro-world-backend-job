<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Reference;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'Resume')]
class DeleteReferenceController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/v1/resume/reference/{reference}', methods: [Request::METHOD_DELETE])]
    public function __invoke(SymfonyUser $loggedInUser, Reference $reference): JsonResponse
    {
        if ($reference->getUser()->toString() !== $loggedInUser->getId()) {
            throw new AccessDeniedHttpException('You cannot delete this reference.');
        }

        $this->entityManager->remove($reference);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'reference deleted']);
    }
}
