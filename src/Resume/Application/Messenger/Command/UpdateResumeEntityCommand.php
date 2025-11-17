<?php

declare(strict_types=1);

namespace App\Resume\Application\Messenger\Command;

final class UpdateResumeEntityCommand
{
    public function __construct(
        private readonly string $resource,
        private readonly string $entityId,
        private readonly ?string $userId,
        private readonly array $payload
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

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
