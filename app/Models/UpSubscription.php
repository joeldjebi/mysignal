<?php

namespace App\Models;

use App\Domain\Subscriptions\Enums\UpSubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_user_id',
        'subscription_plan_id',
        'renewed_from_subscription_id',
        'status',
        'start_date',
        'end_date',
        'grace_period_days',
        'amount',
        'currency',
        'activated_at',
        'expired_at',
        'cancelled_at',
        'suspended_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'activated_at' => 'datetime',
            'expired_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'suspended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function renewedFromSubscription(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_subscription_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isPending(): bool
    {
        return $this->status === UpSubscriptionStatus::Pending->value;
    }

    public function isActive(): bool
    {
        return $this->status === UpSubscriptionStatus::Active->value;
    }
}
