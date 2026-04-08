<?php

namespace App\Support;

use App\Models\Application;
use Illuminate\Support\Collection;

class ApplicationCatalog
{
    protected static ?Collection $applications = null;

    public static function active(): Collection
    {
        if (self::$applications === null) {
            self::$applications = Application::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return self::$applications;
    }

    public static function findByNetworkType(?string $networkType): ?Application
    {
        $code = match (strtoupper((string) $networkType)) {
            'CIE' => 'MON_NRJ',
            'SODECI' => 'MON_EAU',
            default => strtoupper((string) $networkType),
        };

        return self::active()->firstWhere('code', $code);
    }

    public static function networkTypeForApplicationCode(?string $applicationCode): ?string
    {
        return strtoupper((string) $applicationCode) ?: null;
    }
}
