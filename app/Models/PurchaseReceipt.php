<?php

namespace App\Models;

use App\Services\WasabiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReceipt extends Model
{
    protected $fillable = [
        'public_user_id',
        'material_name',
        'purchase_date',
        'amount',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'amount' => 'decimal:2',
            'attachment' => 'array',
        ];
    }

    public function publicUser(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function resolvedAttachment(): mixed
    {
        if (! is_array($this->attachment)) {
            return $this->attachment;
        }

        $attachment = $this->attachment;
        $attachment['temporary_url'] = $attachment['temporary_url']
            ?? (filled($attachment['path'] ?? null) ? app(WasabiService::class)->temporaryUrl($attachment['path']) : null);

        return $attachment;
    }
}
