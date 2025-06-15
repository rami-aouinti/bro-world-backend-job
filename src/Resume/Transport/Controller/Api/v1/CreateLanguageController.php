<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Language;
use App\Resume\Infrastructure\Repository\LanguageRepository;
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
class CreateLanguageController extends AbstractController
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
        path: '/v1/resume/language',
        methods: [Request::METHOD_POST],
    )]
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        HubInterface $hub,
        LanguageRepository $languageRepository
    ): JsonResponse {

        $language = $languageRepository->findOneBy([
            'name' => $request->request->get('language')
        ]);

        if($language) {
           $language->setLevel((int)$request->request->get('level'));
        } else {
            $language = new Language();
            $language->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
            $language->setName($request->request->get('language'));
            $language->setLevel((int)$request->request->get('level'));
            $language->setFlag($request->request->get('flag'));
        }

        $this->entityManager->persist($language);

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
