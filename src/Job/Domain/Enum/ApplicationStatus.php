<?php

namespace App\Job\Domain\Enum;

enum ApplicationStatus: string
{
    case Request = 'Request';
    case Progress = 'Progress';
    case Accept = 'Accept';
    case Declined = 'Declined';
}
