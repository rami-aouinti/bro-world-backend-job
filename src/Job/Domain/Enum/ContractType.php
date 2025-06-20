<?php

namespace App\Job\Domain\Enum;

enum ContractType: string
{
    case FULLTIME = 'fulltime';
    case PARTTIME = 'parttime';
}
