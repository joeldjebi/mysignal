<?php

namespace App\Models;

use App\Services\WasabiService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'tagline',
        'short_description',
        'long_description',
        'logo_path',
        'hero_image_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)
            ->withTimestamps();
    }

    public function signalTypes(): HasMany
    {
        return $this->hasMany(SignalType::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function contentBlocks(): HasMany
    {
        return $this->hasMany(ApplicationContentBlock::class);
    }

    public function logoUrl(): ?string
    {
        return $this->resolveAssetUrl($this->logo_path);
    }

    public function heroImageUrl(): ?string
    {
        return $this->resolveAssetUrl($this->hero_image_path);
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with((string) $path, 'applications/')) {
            return app(WasabiService::class)->temporaryUrl($path);
        }

        return asset((string) $path);
    }
}
