<?php

namespace App\Domain\Auth\Data;

class OtpRequestResult
{
    public function __construct(
        public readonly string $phone,
        public readonly string $code,
        public readonly string $expiresAt,
    ) {
    }
}
