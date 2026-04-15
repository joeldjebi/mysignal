<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RexFeedback extends Model
{
    protected $fillable = [
        'public_user_id',
        'incident_report_id',
        'application_id',
        'organization_id',
        'context_type',
        'context_id',
        'rating',
        'is_resolved',
        'response_time_rating',
        'communication_rating',
        'quality_rating',
        'fairness_rating',
        'comment',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'submitted_at' => 'datetime',
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

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
