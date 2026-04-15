<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RexSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'incident_report_enabled',
        'damage_enabled',
        'reparation_case_enabled',
        'available_days',
        'editable_hours',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'incident_report_enabled' => 'boolean',
            'damage_enabled' => 'boolean',
            'reparation_case_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'is_enabled' => true,
            'incident_report_enabled' => true,
            'damage_enabled' => true,
            'reparation_case_enabled' => true,
            'available_days' => 30,
            'editable_hours' => 24,
        ]);
    }
}
