<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Job;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\CompanyRepository;
use App\Job\Infrastructure\Repository\JobRepository;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @package App\Job
 */
#[AsController]
#[OA\Tag(name: 'Job')]
class CreateJobController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly JobRepository $jobRepository,
        private readonly CompanyRepository $companyRepository
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/job',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(SymfonyUser $loggedInUser, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company = $this->companyRepository->find($jsonParams['companyId']);

        $job = new Job();
        $job->setTitle($jsonParams['title']);
        $job->setDescription($jsonParams['description']);
        $job->setRequiredSkills($jsonParams['requiredSkills']);
        $job->setCompany($company);
        $job->setExperience($jsonParams['experience']);

        $violations = $validator->validate($job);

        if(count($violations) === 0){
            $this->jobRepository->save($job, true);
        }

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $job,
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
