<?php

namespace App\Job\Domain\Enum;

enum LanguageLevel: string
{
    case BASIC = 'basic';
    case INTERMEDIATE = 'intermediate';
    case FLUENT = 'fluent';
    case NATIVE = 'native';
}
