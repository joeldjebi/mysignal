<?php

namespace App\Domain\Auth\Enums;

enum OtpPurpose: string
{
    case Registration = 'registration';
    case PasswordReset = 'password_reset';
}
