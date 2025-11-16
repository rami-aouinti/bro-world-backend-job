<?php

namespace App\Job\Application\Messenger\Handler;

use App\Job\Application\Messenger\Command\DeleteJobCommand;
use App\Job\Application\Service\JobCacheKeyGenerator;
use App\Job\Domain\Entity\Job;
use App\Job\Infrastructure\Repository\JobRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class DeleteJobHandler
{
    public function __construct(
        private readonly JobRepository $jobRepository,
        private readonly TagAwareCacheInterface $cache
    ) {
    }

    public function __invoke(DeleteJobCommand $command): Job
    {
        $job = $this->jobRepository->find($command->getJobId());

        if ($job === null) {
            throw new NotFoundHttpException(sprintf('Job %s not found', $command->getJobId()));
        }

        $this->jobRepository->remove($job, true);

        $this->cache->deleteItem(JobCacheKeyGenerator::buildJobItemKey($command->getJobId()));
        $this->cache->invalidateTags([
            JobCacheKeyGenerator::JOB_LIST_TAG,
            JobCacheKeyGenerator::jobItemTag($command->getJobId()),
        ]);

        return $job;
    }
}
