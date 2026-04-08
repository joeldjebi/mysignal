<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicUserType extends Model
{
    use HasFactory;

    protected $fillable = [
        'pricing_rule_id',
        'code',
        'name',
        'description',
        'profile_kind',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }

    public function publicUsers(): HasMany
    {
        return $this->hasMany(PublicUser::class);
    }
}
