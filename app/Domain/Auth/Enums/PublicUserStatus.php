<?php

namespace App\Domain\Auth\Enums;

enum PublicUserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
