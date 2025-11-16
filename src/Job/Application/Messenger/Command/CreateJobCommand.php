<?php

namespace App\Job\Application\Messenger\Command;

final class CreateJobCommand
{
    public function __construct(
        private readonly string $title,
        private readonly string $description,
        private readonly string $requiredSkills,
        private readonly string $experience,
        private readonly string $companyId
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getRequiredSkills(): string
    {
        return $this->requiredSkills;
    }

    public function getExperience(): string
    {
        return $this->experience;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }
}
