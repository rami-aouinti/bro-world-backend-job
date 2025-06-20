<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Hobby;
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
class CreateHobbyController extends AbstractController
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
        path: '/v1/resume/hobby',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        HubInterface $hub
    ): JsonResponse {
        $hobby = new Hobby();
        $hobby->setName($request->request->get('name'));
        $hobby->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
        $hobby->setIcon($request->request->get('icon'));

        $this->entityManager->persist($hobby);
        $this->entityManager->flush();


        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $hobby,
                'json',
                [ 'groups' => 'Hobby',]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
