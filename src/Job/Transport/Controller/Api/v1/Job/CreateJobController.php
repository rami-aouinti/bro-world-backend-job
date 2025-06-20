<?php

declare(strict_types=1);

namespace App\Job\Transport\Controller\Api\v1\Job;

use App\General\Domain\Utils\JSON;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Job\Domain\Entity\Job;
use App\Job\Domain\Entity\Language;
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
use Ramsey\Uuid\Uuid;

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
        private readonly CompanyRepository $companyRepository,
        private readonly ValidatorInterface $validator
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
    public function __invoke(SymfonyUser $loggedInUser, Request $request): JsonResponse
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company = $this->companyRepository->find($jsonParams['companyId']);

        $job = new Job();
        $job->setTitle($jsonParams['title']);
        $job->setDescription($jsonParams['description']);
        $job->setRequiredSkills($jsonParams['requiredSkills']);
        $job->setWorkType($jsonParams['workType'] ?? '');
        $job->setWorkLocation($jsonParams['workLocation'] ?? '');
        $job->setSalaryRange($jsonParams['salaryRange'] ?? '');
        $job->setContractType($jsonParams['contractType'] ?? '');
        $job->setRequirements($jsonParams['requirements'] ?? '');
        $job->setBenefits($jsonParams['benefits'] ?? '');
        if(isset($jsonParams['languages'] )) {
            foreach ($jsonParams['languages'] as $languageForm) {
                $language = new Language();
                $language->setName($languageForm['name']);
                $language->setLevel($languageForm['level']);
                $job->addLanguage($language);
            }
        }
        $job->setCompany($company);
        $job->setUser(Uuid::fromString($loggedInUser->getUserIdentifier()));
        $job->setExperience($jsonParams['experience']);

        $violations = $this->validator->validate($job);

        $this->jobRepository->save($job, true);


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
