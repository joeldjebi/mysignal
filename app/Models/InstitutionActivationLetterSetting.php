<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionActivationLetterSetting extends Model
{
    public const DEFAULT_KEY = 'default';

    protected $fillable = [
        'key',
        'updated_by',
        'logo_position',
        'logo_path',
        'signature_name',
        'signature_title',
        'signature_content',
        'signature_path',
        'footer_logo_path',
        'header_settings',
        'footer_settings',
    ];

    protected $casts = [
        'header_settings' => 'array',
        'footer_settings' => 'array',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): ?self
    {
        return self::query()->where('key', self::DEFAULT_KEY)->first();
    }

    public static function attributesFromLetter(InstitutionActivationLetter $letter, ?int $updatedBy = null): array
    {
        return [
            'updated_by' => $updatedBy,
            'logo_position' => $letter->logo_position ?: 'left',
            'logo_path' => $letter->logo_path,
            'signature_name' => $letter->signature_name,
            'signature_title' => $letter->signature_title,
            'signature_content' => $letter->signature_content,
            'signature_path' => $letter->signature_path,
            'footer_logo_path' => $letter->footer_logo_path,
            'header_settings' => $letter->header_settings,
            'footer_settings' => $letter->footer_settings,
        ];
    }

    public function presentationDefaults(): array
    {
        $blankLetter = new InstitutionActivationLetter();

        return [
            'logo_position' => $this->logo_position ?: 'left',
            'logo_path' => $this->logo_path,
            'signature_name' => $this->signature_name ?: 'Le Coordonnateur du programme My-Signal',
            'signature_title' => $this->signature_title ?: 'Union Fédérale des Consommateurs',
            'signature_content' => $this->signature_content ?: '<p>Pour l’Union Fédérale des Consommateurs</p>',
            'signature_path' => $this->signature_path,
            'footer_logo_path' => $this->footer_logo_path,
            'header_settings' => array_replace_recursive($blankLetter->defaultHeaderSettings(), $this->header_settings ?? []),
            'footer_settings' => array_replace_recursive($blankLetter->defaultFooterSettings(), $this->footer_settings ?? []),
        ];
    }
}
