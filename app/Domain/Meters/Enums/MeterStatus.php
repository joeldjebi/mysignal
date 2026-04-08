<?php

namespace App\Domain\Meters\Enums;

enum MeterStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
