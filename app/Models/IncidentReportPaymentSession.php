<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentReportPaymentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_user_id',
        'pricing_rule_id',
        'incident_report_id',
        'sync_ref',
        'amount',
        'currency',
        'status',
        'provider',
        'provider_reference',
        'checkout_link',
        'report_payload',
        'signal_attachment',
        'metadata',
        'initiated_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'report_payload' => 'array',
            'signal_attachment' => 'array',
            'metadata' => 'array',
            'initiated_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }

    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }
}
