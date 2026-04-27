<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'recipient_type',
    'recipient_id',
    'guard',
    'token',
    'token_hash',
    'platform',
    'device_name',
    'app_version',
    'last_seen_at',
    'revoked_at',
])]
class DeviceToken extends Model
{
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
