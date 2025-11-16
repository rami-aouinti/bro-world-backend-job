<?php

namespace App\Job\Application\Service;

final class JobCacheKeyGenerator
{
    public const JOB_LIST_TAG = 'job_list';
    private const JOB_LIST_PREFIX = 'jobs_';
    private const JOB_ITEM_PREFIX = 'job_';
    private const JOB_ITEM_TAG_PREFIX = 'job_item_';

    public static function buildJobListKey(array $filters): string
    {
        ksort($filters);

        return self::JOB_LIST_PREFIX . md5(json_encode($filters));
    }

    public static function buildJobItemKey(string $jobId): string
    {
        return self::JOB_ITEM_PREFIX . $jobId;
    }

    public static function jobItemTag(string $jobId): string
    {
        return self::JOB_ITEM_TAG_PREFIX . $jobId;
    }
}
