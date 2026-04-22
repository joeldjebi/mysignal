<?php

namespace App\Domain\Auth\Enums;

enum PartnerOtpPurpose: string
{
    case PasswordReset = 'password_reset';
}
