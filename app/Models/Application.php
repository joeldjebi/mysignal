<?php

namespace App\Models;

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
}
