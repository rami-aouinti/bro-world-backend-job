<?php

declare(strict_types=1);

namespace App\Resume\Application\Messenger\Handler;

use App\Resume\Application\Messenger\Command\DeleteResumeEntityCommand;
use App\Resume\Application\Service\ResumeEntityManager;

final class DeleteResumeEntityHandler
{
    public function __construct(private readonly ResumeEntityManager $entityManager)
    {
    }

    public function __invoke(DeleteResumeEntityCommand $command): object
    {
        return $this->entityManager->delete(
            $command->getResource(),
            $command->getEntityId(),
            $command->getUserId()
        );
    }
}
