<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'organization_id',
        'network_type',
        'meter_number',
        'label',
        'commune',
        'neighborhood',
        'sub_neighborhood',
        'address',
        'latitude',
        'longitude',
        'location_accuracy',
        'location_source',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'location_accuracy' => 'integer',
        ];
    }

    public function publicUsers(): BelongsToMany
    {
        return $this->belongsToMany(PublicUser::class, 'meter_assignments')
            ->withPivot(['is_primary'])
            ->withTimestamps();
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }
}
