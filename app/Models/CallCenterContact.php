<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CallCenterContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contactable_type',
        'contactable_id',
        'public_user_id',
        'called_by_user_id',
        'context',
        'comment',
        'called_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function calledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by_user_id');
    }
}
