<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationTypeSignalSla extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_type_id',
        'network_type',
        'signal_code',
        'signal_label',
        'sla_hours',
        'description',
        'status',
    ];

    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class);
    }
}
