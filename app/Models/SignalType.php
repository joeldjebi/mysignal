<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SignalType extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'organization_id',
        'network_type',
        'code',
        'label',
        'description',
        'default_sla_hours',
        'requires_public_user_identifier',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default_sla_hours' => 'integer',
            'requires_public_user_identifier' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withTimestamps();
    }

    public function subTypes(): HasMany
    {
        return $this->hasMany(SignalSubType::class);
    }
}
