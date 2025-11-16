<?php

namespace App\Job\Application\Messenger\Command;

final class DeleteJobCommand
{
    public function __construct(private readonly string $jobId)
    {
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}
