<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Application;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Domain\Entity\Applicant;
use App\Job\Domain\Entity\Company;
use App\Job\Domain\Entity\JobApplication;
use App\Job\Domain\Enum\ApplicationStatus;
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
 * @package App\Applicant
 */
#[AsController]
#[OA\Tag(name: 'Applicant')]
readonly class AcceptApplicationController
{
    public function __construct(
        private SerializerInterface $serializer,
        private JobApplicationRepository $jobApplicationRepository
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/application/{status}/{application}',
        methods: [Request::METHOD_PUT],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request, string $status ,JobApplication $application): JsonResponse
    {
        if ($status === 'accept') {
            $application->setStatus(ApplicationStatus::Accept);
        }
        if ($status === 'declined') {
            $application->setStatus(ApplicationStatus::Declined);
        }
        if ($status === 'progress') {
            $application->setStatus(ApplicationStatus::Progress);
        }
        $application->setStatus(ApplicationStatus::Accept);
        $this->jobApplicationRepository->save($application, true);

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $application,
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
