<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Hobby;
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
class DeleteHobbyController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/v1/resume/hobby/{hobby}', methods: [Request::METHOD_DELETE])]
    public function __invoke(SymfonyUser $loggedInUser, Hobby $hobby): JsonResponse
    {
        if ($hobby->getUser()->toString() !== $loggedInUser->getId()) {
            throw new AccessDeniedHttpException('You cannot delete this hobby.');
        }

        $this->entityManager->remove($hobby);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'hobby deleted']);
    }
}
