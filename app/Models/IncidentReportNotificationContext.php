<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentReportNotificationContext extends Model
{
    protected $fillable = [
        'incident_report_id',
        'context_type',
        'household_id',
        'organization_id',
        'meter_id',
        'signal_code',
        'latitude',
        'longitude',
        'radius_meters',
        'recipient_public_user_ids',
        'notified_at',
        'resolved_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'recipient_public_user_ids' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_meters' => 'integer',
            'notified_at' => 'datetime',
            'resolved_notified_at' => 'datetime',
        ];
    }

    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }
}
