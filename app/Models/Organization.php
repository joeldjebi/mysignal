<?php

namespace App\Models;

use App\Services\WasabiService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'organization_type_id',
        'code',
        'name',
        'portal_key',
        'email',
        'phone',
        'commune',
        'address',
        'description',
        'logo_path',
        'status',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function partnerDiscountOffers(): HasMany
    {
        return $this->hasMany(PartnerDiscountOffer::class);
    }

    public function partnerDiscountTransactions(): HasMany
    {
        return $this->hasMany(PartnerDiscountTransaction::class);
    }

    public function featureOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)
            ->withPivot('enabled')
            ->withTimestamps();
    }

    public function signalTypes(): BelongsToMany
    {
        return $this->belongsToMany(SignalType::class)
            ->withTimestamps();
    }

    public function resolvedFeatures(): Collection
    {
        $applicationFeatures = $this->application?->relationLoaded('features')
            ? $this->application->features
            : $this->application?->features()->where('status', 'active')->get();
        $applicationFeatures ??= collect();

        $overrides = $this->relationLoaded('featureOverrides')
            ? $this->featureOverrides
            : $this->featureOverrides()->get();

        $enabledOverrideFeatures = $overrides
            ->filter(fn (Feature $feature) => (bool) ($feature->pivot?->enabled ?? true))
            ->values();

        $disabledOverrideIds = $overrides
            ->filter(fn (Feature $feature) => ! (bool) ($feature->pivot?->enabled ?? true))
            ->pluck('id')
            ->unique()
            ->values();

        return $applicationFeatures
            ->merge($enabledOverrideFeatures)
            ->filter(fn (Feature $feature) => $feature->status === 'active')
            ->unique('id')
            ->reject(fn (Feature $feature) => $disabledOverrideIds->contains($feature->id))
            ->values();
    }

    public function resolvedFeatureIds(): array
    {
        return $this->resolvedFeatures()->pluck('id')->all();
    }

    public function resolvedFeatureCodes(): array
    {
        return $this->resolvedFeatures()->pluck('code')->all();
    }

    public function logoUrl(): ?string
    {
        if (! filled($this->logo_path)) {
            return null;
        }

        if (filter_var($this->logo_path, FILTER_VALIDATE_URL)) {
            return $this->logo_path;
        }

        if (str_starts_with((string) $this->logo_path, 'organizations/')) {
            return app(WasabiService::class)->temporaryUrl($this->logo_path);
        }

        return asset((string) $this->logo_path);
    }
}
