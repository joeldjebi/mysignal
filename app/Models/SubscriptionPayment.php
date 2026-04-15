<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_user_id',
        'up_subscription_id',
        'reference',
        'amount',
        'currency',
        'status',
        'provider',
        'provider_reference',
        'initiated_at',
        'paid_at',
        'failed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'initiated_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UpSubscription::class, 'up_subscription_id');
    }
}
