<?php

declare(strict_types=1);

namespace App\Resume\Application\Messenger\Command;

final class DeleteResumeEntityCommand
{
    public function __construct(
        private readonly string $resource,
        private readonly string $entityId,
        private readonly ?string $userId
    ) {
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }
}
