<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\CompanyRepository;
use App\Resume\Infrastructure\Repository\ExperienceRepository;
use App\Resume\Infrastructure\Repository\FormationRepository;
use App\Resume\Infrastructure\Repository\HobbyRepository;
use App\Resume\Infrastructure\Repository\LanguageRepository;
use App\Resume\Infrastructure\Repository\ProjectRepository;
use App\Resume\Infrastructure\Repository\SkillRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Company
 */
#[AsController]
#[OA\Tag(name: 'Company')]
readonly class GetResumeController
{
    public function __construct(
        private SerializerInterface $serializer,
        private FormationRepository $formationRepository,
        private ExperienceRepository $experienceRepository,
        private SkillRepository $skillRepository,
        private LanguageRepository $languageRepository,
        private ProjectRepository $projectRepository,
        private HobbyRepository $hobbyRepository
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/profile/resume',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $response = [];
        $response['educations'] = $this->formationRepository->findBy([
            'user' => $loggedInUser->getUserIdentifier(),
        ]);
        $response['experiences'] = $this->experienceRepository->findBy([
            'user' => $loggedInUser->getUserIdentifier(),
        ]);
        $response['skills'] = $this->skillRepository->findBy([
            'user' => $loggedInUser->getUserIdentifier(),
        ]);
        $response['languages'] = $this->languageRepository->findBy([
            'user' => $loggedInUser->getUserIdentifier(),
        ]);
        $response['hobbies'] = $this->hobbyRepository->findBy([
            'user' => $loggedInUser->getUserIdentifier(),
        ]);

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $response,
                'json',
                [
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
