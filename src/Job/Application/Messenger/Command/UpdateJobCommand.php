<?php

namespace App\Job\Application\Messenger\Command;

final class UpdateJobCommand
{
    public function __construct(
        private readonly string $jobId,
        private readonly ?string $title = null,
        private readonly ?string $description = null,
        private readonly ?string $requiredSkills = null,
        private readonly ?string $experience = null,
        private readonly ?string $companyId = null
    ) {
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getRequiredSkills(): ?string
    {
        return $this->requiredSkills;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }
}
