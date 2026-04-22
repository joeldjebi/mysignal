<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerUserPhoneVerification extends Model
{
    protected $fillable = [
        'phone',
        'verified_at',
        'expires_at',
        'token',
        'used_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
