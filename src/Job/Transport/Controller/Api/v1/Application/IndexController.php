<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Application;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Infrastructure\Repository\ApplicantRepository;
use App\Job\Infrastructure\Repository\CompanyRepository;
use App\Job\Infrastructure\Repository\JobApplicationRepository;
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
#[OA\Tag(name: 'Applicant')]
readonly class IndexController
{
    public function __construct(
        private SerializerInterface $serializer,
        private JobApplicationRepository $jobApplicationRepository,
        private UserProxy           $userProxy
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/application',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $users = $this->userProxy->getUsers();

        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }

        $applicants = $this->jobApplicationRepository->findAll();
        $response = [];
        foreach ($applicants as $key => $applicant){
            $response[$key] = $applicant->toArray();
            $response[$key]['user'] = $usersById[$applicant->getApplicant()?->getUser()->toString()] ?? null;
        }

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $response,
                'json',
                [
                    'groups' => 'Application',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
