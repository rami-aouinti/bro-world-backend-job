<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Application;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Domain\Entity\Applicant;
use App\Job\Domain\Entity\Company;
use App\Job\Domain\Entity\Job;
use App\Job\Domain\Entity\JobApplication;
use App\Job\Infrastructure\Repository\CompanyRepository;
use App\Job\Infrastructure\Repository\JobApplicationRepository;
use App\Job\Infrastructure\Repository\JobRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @package App\Application
 */
#[AsController]
#[OA\Tag(name: 'Application')]
class CreateApplicationController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly JobApplicationRepository $jobApplicationRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/application/{job}/{applicant}',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        Job $job,
        Applicant $applicant
    ): JsonResponse
    {
        $jobApplication = new JobApplication();
        $jobApplication->setJob($job);
        $jobApplication->setApplicant($applicant);
        $violations = $this->validator->validate($jobApplication);
        $this->jobApplicationRepository->save($jobApplication, true);
        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $jobApplication,
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
