<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerDiscountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'up_discount_card_id',
        'partner_discount_offer_id',
        'organization_id',
        'partner_user_id',
        'public_user_id',
        'up_subscription_id',
        'scan_reference',
        'verification_status',
        'status',
        'original_amount',
        'discount_amount',
        'final_amount',
        'discount_type_snapshot',
        'discount_value_snapshot',
        'applied_at',
        'cancelled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'discount_value_snapshot' => 'decimal:2',
            'applied_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function discountCard(): BelongsTo
    {
        return $this->belongsTo(UpDiscountCard::class, 'up_discount_card_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(PartnerDiscountOffer::class, 'partner_discount_offer_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function partnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_user_id');
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
