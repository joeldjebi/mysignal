<?php

namespace App\Models;

use App\Services\WasabiService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_user_id',
        'application_id',
        'organization_id',
        'assigned_to_user_id',
        'meter_id',
        'country_id',
        'city_id',
        'commune_id',
        'address',
        'latitude',
        'longitude',
        'location_accuracy',
        'location_source',
        'network_type',
        'signal_code',
        'signal_label',
        'incident_type',
        'reference',
        'description',
        'signal_payload',
        'target_sla_hours',
        'occurred_at',
        'status',
        'payment_status',
        'paid_at',
        'taken_in_charge_at',
        'resolved_at',
        'official_response',
        'resolution_confirmation_status',
        'resolution_confirmed_at',
        'damage_summary',
        'damage_amount_estimated',
        'damage_notes',
        'damage_attachment',
        'damage_declared_at',
        'damage_resolution_status',
        'damage_resolution_notes',
        'damage_resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'paid_at' => 'datetime',
            'taken_in_charge_at' => 'datetime',
            'resolved_at' => 'datetime',
            'resolution_confirmed_at' => 'datetime',
            'damage_declared_at' => 'datetime',
            'damage_resolved_at' => 'datetime',
            'signal_payload' => 'array',
            'damage_attachment' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'location_accuracy' => 'integer',
            'damage_amount_estimated' => 'decimal:2',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function resolvedSignalPayload(): array
    {
        return collect($this->signal_payload ?? [])
            ->map(fn ($value) => $this->resolveStoredFilePayload($value))
            ->all();
    }

    public function resolvedDamageAttachment(): mixed
    {
        return $this->resolveStoredFilePayload($this->damage_attachment);
    }

    private function resolveStoredFilePayload(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $resolvedValue = $value;
        $resolvedValue['temporary_url'] = $value['temporary_url']
            ?? $value['data_url']
            ?? (filled($value['path'] ?? null) ? app(WasabiService::class)->temporaryUrl($value['path']) : null);

        return $resolvedValue;
    }
}
