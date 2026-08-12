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
        'letter_number',
        'issue_place',
        'issue_date',
        'letter_subject',
        'letter_content',
        'signature_name',
        'signature_title',
        'signature_content',
        'signature_path',
        'footer_logo_path',
        'logo_position',
        'logo_path',
        'header_settings',
        'footer_settings',
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
        'issue_date' => 'date',
        'submitted_at' => 'datetime',
        'metadata' => 'array',
        'header_settings' => 'array',
        'footer_settings' => 'array',
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

    public function signatureUrl(): ?string
    {
        if (! filled($this->signature_path)) {
            return null;
        }

        return app(WasabiService::class)->temporaryUrl($this->signature_path);
    }

    public function footerLogoUrl(): ?string
    {
        if (! filled($this->footer_logo_path)) {
            return null;
        }

        return app(WasabiService::class)->temporaryUrl($this->footer_logo_path);
    }

    public function contentHtml(): HtmlString
    {
        return $this->safeHtml((string) $this->letter_content);
    }

    public function safeHtml(string $content): HtmlString
    {
        $content = (string) $content;

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

    public function headerSettings(): array
    {
        return array_replace_recursive($this->defaultHeaderSettings(), $this->header_settings ?? []);
    }

    public function footerSettings(): array
    {
        return array_replace_recursive($this->defaultFooterSettings(), $this->footer_settings ?? []);
    }

    public function defaultHeaderSettings(): array
    {
        return [
            'logo_width' => 145,
            'show_platform_logo' => false,
            'platform_logo_width' => 72,
            'title' => [
                'text' => 'UNION FÉDÉRALE DES CONSOMMATEURS',
                'size' => 18,
                'color' => '#101828',
                'bold' => true,
                'italic' => false,
            ],
            'subtitle' => [
                'text' => "DE CÔTE D'IVOIRE",
                'size' => 17,
                'color' => '#101828',
                'bold' => true,
                'italic' => false,
            ],
            'description' => [
                'text' => "UFC - Côte d’Ivoire • Association de défense des consommateurs",
                'size' => 11,
                'color' => '#667085',
                'bold' => false,
                'italic' => false,
            ],
        ];
    }

    public function defaultFooterSettings(): array
    {
        return [
            'logo' => [
                'label' => 'Logo',
                'size' => 72,
            ],
            'address' => [
                'label' => 'Adresse',
                'text' => 'Abidjan, Côte d’Ivoire',
                'size' => 10,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
            'phone' => [
                'label' => 'Téléphone',
                'text' => '',
                'size' => 10,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
            'email' => [
                'label' => 'Email',
                'text' => '',
                'size' => 10,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
            'website' => [
                'label' => 'Site web',
                'text' => 'https://my-signal.pro',
                'size' => 10,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
        ];
    }
}
