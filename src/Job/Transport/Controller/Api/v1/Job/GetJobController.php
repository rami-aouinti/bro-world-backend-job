<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Job;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Application\ApiProxy\UserProxy;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\ApplicantRepository;
use App\Job\Infrastructure\Repository\JobApplicationRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Job
 */
#[AsController]
#[OA\Tag(name: 'Job')]
readonly class GetJobController
{
    public function __construct(
        private SerializerInterface $serializer,
        private UserProxy           $userProxy,
        private JobApplicationRepository $jobApplicationRepository,
        private ApplicantRepository $applicantRepository
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/job/{job}',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request, Job $job): JsonResponse
    {
        // Sinon, fallback sur DQL classique
        $users = $this->userProxy->getUsers();
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }
        $applicants = $this->applicantRepository->findBy([
            'user' => $loggedInUser->getUserIdentifier(),
        ]);
        $response = [];
        $applied = null;
        if(!empty($applicants)) {
            foreach ($applicants as $applicant) {
                $applied = $this->jobApplicationRepository->findOneBy([
                    'applicant' => $applicant->getId(),
                    'job' => $job->getId(),
                ]);
            }
        }
        $response = $job->toArray();
        $response['owner'] = $job->getUser()->toString() === $loggedInUser->getUserIdentifier();
        $response['applied'] = $applied !== null;
        $response['user'] = $usersById[$job->getUser()->toString()] ?? null;

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $response,
                'json',
                [
                    'groups' => 'Job',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
