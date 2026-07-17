<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeCardPaymentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_user_id',
        'privilege_card_type_id',
        'privilege_card_id',
        'sync_ref',
        'amount',
        'currency',
        'status',
        'provider',
        'provider_reference',
        'checkout_link',
        'metadata',
        'initiated_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'initiated_at' => 'datetime',
            'paid_at' => 'datetime',
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

    public function card(): BelongsTo
    {
        return $this->belongsTo(PrivilegeCard::class, 'privilege_card_id');
    }
}
