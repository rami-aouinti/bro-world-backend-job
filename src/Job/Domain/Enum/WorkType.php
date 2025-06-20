<?php

namespace App\Job\Domain\Enum;

enum WorkType: string
{
    case REMOTE = 'Remote';
    case HYBRID = 'Hybrid';
    case ONSITE = 'Onsite';
}
