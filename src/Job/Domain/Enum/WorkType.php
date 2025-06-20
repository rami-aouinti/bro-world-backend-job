<?php

namespace App\Job\Domain\Enum;

enum WorkType: string
{
    case REMOTE = 'remote';
    case HYBRID = 'hybrid';
    case ONSITE = 'onsite';
}
