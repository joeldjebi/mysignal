<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReparationCaseStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'reparation_case_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'step_type',
        'status',
        'title',
        'summary',
        'due_at',
        'completed_at',
        'is_visible_to_public',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_visible_to_public' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function reparationCase(): BelongsTo
    {
        return $this->belongsTo(ReparationCase::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
