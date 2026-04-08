<?php

namespace App\Models;

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
        'description',
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

    public function featureOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)
            ->withPivot('enabled')
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
}
