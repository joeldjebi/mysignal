<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignalSubType extends Model
{
    use HasFactory;

    protected $fillable = [
        'signal_type_id',
        'code',
        'label',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function signalType(): BelongsTo
    {
        return $this->belongsTo(SignalType::class);
    }
}
