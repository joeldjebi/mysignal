<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdInvitation extends Model
{
    protected $fillable = [
        'household_id',
        'meter_id',
        'phone',
        'relationship',
        'code',
        'expires_at',
        'accepted_at',
        'declined_at',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    protected $hidden = [
        'code',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class, 'invited_by');
    }
}
