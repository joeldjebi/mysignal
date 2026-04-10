<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReparationCaseHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'reparation_case_id',
        'created_by_user_id',
        'event_type',
        'title',
        'description',
        'is_visible_to_public',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_visible_to_public' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function reparationCase(): BelongsTo
    {
        return $this->belongsTo(ReparationCase::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
