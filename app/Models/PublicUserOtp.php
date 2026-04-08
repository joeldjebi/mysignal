<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicUserOtp extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'purpose',
        'expires_at',
        'verified_at',
        'attempts',
        'max_attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}
