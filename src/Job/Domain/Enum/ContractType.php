<?php

namespace App\Job\Domain\Enum;

enum ContractType: string
{
    case FULLTIME = 'Fulltime';
    case PARTTIME = 'Parttime';
}
