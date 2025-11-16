<?php

namespace App\Job\Application\Messenger\Handler;

use App\Job\Application\Messenger\Command\CreateJobCommand;
use App\Job\Application\Service\JobCacheKeyGenerator;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\CompanyRepository;
use App\Job\Infrastructure\Repository\JobRepository;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class CreateJobHandler
{
    public function __construct(
        private readonly JobRepository $jobRepository,
        private readonly CompanyRepository $companyRepository,
        private readonly ValidatorInterface $validator,
        private readonly TagAwareCacheInterface $cache
    ) {
    }

    /**
     * @throws ValidationFailedException
     */
    public function __invoke(CreateJobCommand $command): Job
    {
        $company = $this->companyRepository->find($command->getCompanyId());

        if ($company === null) {
            throw new NotFoundHttpException(sprintf('Company %s not found', $command->getCompanyId()));
        }

        $job = new Job();
        $job->setTitle($command->getTitle());
        $job->setDescription($command->getDescription());
        $job->setRequiredSkills($command->getRequiredSkills());
        $job->setCompany($company);
        $job->setExperience($command->getExperience());
        $job->setCreatedAt(new DateTimeImmutable());

        $violations = $this->validator->validate($job);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($job, $violations);
        }

        $this->jobRepository->save($job, true);

        $this->cache->invalidateTags([JobCacheKeyGenerator::JOB_LIST_TAG]);

        return $job;
    }
}
