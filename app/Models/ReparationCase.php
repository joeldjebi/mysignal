<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReparationCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_report_id',
        'public_user_id',
        'application_id',
        'organization_id',
        'opened_by_user_id',
        'assigned_to_user_id',
        'bailiff_user_id',
        'lawyer_user_id',
        'reference',
        'case_type',
        'priority',
        'status',
        'eligibility_reason',
        'opening_notes',
        'damage_summary',
        'damage_amount_claimed',
        'damage_amount_validated',
        'resolution_notes',
        'opened_at',
        'closed_at',
        'closure_reason',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'meta' => 'array',
            'damage_amount_claimed' => 'decimal:2',
            'damage_amount_validated' => 'decimal:2',
        ];
    }

    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function bailiff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bailiff_user_id');
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lawyer_user_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ReparationCaseHistory::class)->latest('id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ReparationCaseStep::class)->latest('id');
    }
}
