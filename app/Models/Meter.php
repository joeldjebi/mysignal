<?php

namespace App\Models;

use App\Services\WasabiService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Throwable;

class Meter extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'organization_id',
        'network_type',
        'meter_number',
        'label',
        'city',
        'commune',
        'neighborhood',
        'sub_neighborhood',
        'address',
        'latitude',
        'longitude',
        'location_accuracy',
        'location_source',
        'identifier_photo_path',
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
            ->withPivot(['is_primary', 'assignment_source'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(MeterAssignment::class);
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

    public function identifierPhotoUrl(): ?string
    {
        if (! filled($this->identifier_photo_path)) {
            return null;
        }

        if (filter_var((string) $this->identifier_photo_path, FILTER_VALIDATE_URL)) {
            return (string) $this->identifier_photo_path;
        }

        try {
            return app(WasabiService::class)->temporaryUrl((string) $this->identifier_photo_path);
        } catch (Throwable $exception) {
            Log::warning('Impossible de generer l URL temporaire de la photo identifiant.', [
                'meter_id' => $this->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
