<?php

namespace App\Domain\Auth\Data;

class OtpRequestResult
{
    public function __construct(
        public readonly string $phone,
        public readonly string $code,
        public readonly string $expiresAt,
        public readonly ?string $smsResponse = null,
        public readonly ?string $smsMsisdn = null,
    ) {
    }
}
