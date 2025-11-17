<?php

declare(strict_types=1);

namespace App\Resume\Application\Messenger\Handler;

use App\Resume\Application\Messenger\Command\UpdateResumeEntityCommand;
use App\Resume\Application\Service\ResumeEntityManager;

final class UpdateResumeEntityHandler
{
    public function __construct(private readonly ResumeEntityManager $entityManager)
    {
    }

    public function __invoke(UpdateResumeEntityCommand $command): object
    {
        return $this->entityManager->update(
            $command->getResource(),
            $command->getEntityId(),
            $command->getUserId(),
            $command->getPayload()
        );
    }
}
