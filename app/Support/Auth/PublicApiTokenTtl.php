<?php

namespace App\Support\Auth;

class PublicApiTokenTtl
{
    public static function minutes(): int
    {
        return max(1, (int) config('services.public_auth.token_ttl_minutes', 1051200));
    }

    public static function seconds(): int
    {
        return self::minutes() * 60;
    }
}
