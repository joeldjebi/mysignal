<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrivilegeCardType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'price',
        'currency',
        'benefits',
        'discount_type',
        'discount_value',
        'duration_months',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'benefits' => 'array',
            'discount_value' => 'decimal:2',
            'duration_months' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function cards(): HasMany
    {
        return $this->hasMany(PrivilegeCard::class);
    }

    public function paymentSessions(): HasMany
    {
        return $this->hasMany(PrivilegeCardPaymentSession::class);
    }
}
