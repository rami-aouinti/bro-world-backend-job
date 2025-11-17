<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\Resume\Domain\Entity\Hobby;
use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
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
class UpdateHobbyController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route(path: '/v1/resume/hobby/{hobby}', methods: [Request::METHOD_PATCH])]
    public function __invoke(SymfonyUser $loggedInUser, Request $request, Hobby $hobby): JsonResponse
    {
        if ($hobby->getUser()->toString() !== $loggedInUser->getId()) {
            throw new AccessDeniedHttpException('You cannot edit this hobby.');
        }

        if (($name = $request->request->get('name')) !== null) {
            $hobby->setName($name);
        }

        if (($icon = $request->request->get('icon')) !== null) {
            $hobby->setIcon($icon);
        }

        $this->entityManager->flush();

        /** @var array<string, mixed> $output */
        $output = JSON::decode(
            $this->serializer->serialize($hobby, 'json', ['groups' => 'Hobby']),
            true,
        );

        return new JsonResponse($output);
    }
}
