<?php

declare(strict_types=1);

namespace App\Resume\Application\Messenger\Handler;

use App\Resume\Application\Messenger\Command\CreateResumeEntityCommand;
use App\Resume\Application\Service\ResumeEntityManager;

final class CreateResumeEntityHandler
{
    public function __construct(private readonly ResumeEntityManager $entityManager)
    {
    }

    public function __invoke(CreateResumeEntityCommand $command): object
    {
        return $this->entityManager->create(
            $command->getResource(),
            $command->getUserId(),
            $command->getPayload()
        );
    }
}
