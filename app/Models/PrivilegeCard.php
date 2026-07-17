<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_user_id',
        'privilege_card_type_id',
        'card_uuid',
        'card_number',
        'status',
        'issued_at',
        'activated_at',
        'expires_at',
        'suspended_at',
        'revoked_at',
        'last_used_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'suspended_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_used_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PrivilegeCardType::class, 'privilege_card_type_id');
    }
}
