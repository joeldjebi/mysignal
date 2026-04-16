<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageSectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'landing_page_section_id',
        'item_key',
        'title',
        'subtitle',
        'body',
        'icon',
        'url',
        'value',
        'meta',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(LandingPageSection::class, 'landing_page_section_id');
    }
}
