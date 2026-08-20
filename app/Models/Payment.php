<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_user_id',
        'incident_report_id',
        'pricing_rule_id',
        'reference',
        'amount',
        'currency',
        'status',
        'provider',
        'payment_context',
        'provider_reference',
        'initiated_at',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'initiated_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }

    public function callCenterContacts(): MorphMany
    {
        return $this->morphMany(CallCenterContact::class, 'contactable');
    }

    public function latestCallCenterContact(): MorphOne
    {
        return $this->morphOne(CallCenterContact::class, 'contactable')->latestOfMany('called_at');
    }
}
