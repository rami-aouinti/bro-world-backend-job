<?php

namespace App\Job\Application\Messenger\Handler;

use App\Job\Application\Messenger\Command\UpdateJobCommand;
use App\Job\Application\Service\JobCacheKeyGenerator;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\CompanyRepository;
use App\Job\Infrastructure\Repository\JobRepository;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class UpdateJobHandler
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
    public function __invoke(UpdateJobCommand $command): Job
    {
        $job = $this->jobRepository->find($command->getJobId());

        if ($job === null) {
            throw new NotFoundHttpException(sprintf('Job %s not found', $command->getJobId()));
        }

        if ($command->getTitle() !== null) {
            $job->setTitle($command->getTitle());
        }

        if ($command->getDescription() !== null) {
            $job->setDescription($command->getDescription());
        }

        if ($command->getRequiredSkills() !== null) {
            $job->setRequiredSkills($command->getRequiredSkills());
        }

        if ($command->getExperience() !== null) {
            $job->setExperience($command->getExperience());
        }

        if ($command->getCompanyId() !== null) {
            $company = $this->companyRepository->find($command->getCompanyId());

            if ($company === null) {
                throw new NotFoundHttpException(sprintf('Company %s not found', $command->getCompanyId()));
            }

            $job->setCompany($company);
        }

        $job->setUpdatedAt(new DateTimeImmutable());

        $violations = $this->validator->validate($job);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($job, $violations);
        }

        $this->jobRepository->save($job, true);

        $this->cache->deleteItem(JobCacheKeyGenerator::buildJobItemKey($command->getJobId()));
        $this->cache->invalidateTags([
            JobCacheKeyGenerator::JOB_LIST_TAG,
            JobCacheKeyGenerator::jobItemTag($command->getJobId()),
        ]);

        return $job;
    }
}
