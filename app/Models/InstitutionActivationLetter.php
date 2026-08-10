<?php

namespace App\Models;

use App\Services\WasabiService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class InstitutionActivationLetter extends Model
{
    protected $fillable = [
        'organization_id',
        'institution_admin_id',
        'created_by',
        'approved_by',
        'activation_code',
        'activation_url',
        'letter_subject',
        'letter_content',
        'signature_name',
        'signature_title',
        'signature_content',
        'logo_position',
        'logo_path',
        'status',
        'expires_at',
        'focal_last_name',
        'focal_first_names',
        'focal_position',
        'focal_phone',
        'focal_email',
        'focal_location',
        'focal_latitude',
        'focal_longitude',
        'location_accuracy',
        'submitted_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'metadata' => 'array',
        'focal_latitude' => 'decimal:8',
        'focal_longitude' => 'decimal:8',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function institutionAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'institution_admin_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logoUrl(): ?string
    {
        if (filled($this->logo_path)) {
            return app(WasabiService::class)->temporaryUrl($this->logo_path);
        }

        return $this->organization?->logoUrl();
    }

    public function contentHtml(): HtmlString
    {
        $content = (string) $this->letter_content;

        if (! Str::contains($content, ['<p', '<div', '<strong', '<b', '<em', '<i', '<ul', '<ol', '<h'])) {
            return new HtmlString(nl2br(e($content)));
        }

        return new HtmlString($content);
    }

    public function signatureHtml(): HtmlString
    {
        $content = (string) ($this->signature_content ?: 'Pour l’Union Fédérale des Consommateurs');

        if (! Str::contains($content, ['<p', '<div', '<strong', '<b', '<em', '<i', '<ul', '<ol', '<h'])) {
            return new HtmlString(nl2br(e($content)));
        }

        return new HtmlString($content);
    }
}
