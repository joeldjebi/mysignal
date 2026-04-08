<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignalType extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'organization_id',
        'network_type',
        'code',
        'label',
        'description',
        'default_sla_hours',
        'data_fields',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default_sla_hours' => 'integer',
            'data_fields' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
