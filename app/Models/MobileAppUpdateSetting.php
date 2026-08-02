<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAppUpdateSetting extends Model
{
    protected $fillable = [
        'app_name',
        'latest_version_android',
        'build_version_android',
        'play_store_url',
        'latest_version_ios',
        'build_version_ios',
        'app_store_url',
        'update_type',
        'messages',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'build_version_android' => 'integer',
            'build_version_ios' => 'integer',
            'messages' => 'array',
        ];
    }

    public static function activeFor(string $appName = 'mysignal'): self
    {
        return self::query()
            ->where('app_name', $appName)
            ->where('status', 'active')
            ->first()
            ?? self::query()->create([
                'app_name' => $appName,
                'latest_version_android' => '1.0.5',
                'build_version_android' => 5,
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.bwan.mysignal',
                'latest_version_ios' => '1.0.5',
                'build_version_ios' => 5,
                'app_store_url' => 'https://apps.apple.com/ci/app/my-signal/id6764384980',
                'update_type' => 'minor',
                'messages' => self::defaultMessages(),
                'status' => 'active',
            ]);
    }

    public static function defaultMessages(): array
    {
        return [
            'minor' => [
                'title' => 'Nouvelle version disponible',
                'message' => 'Une nouvelle version est disponible avec des améliorations et des corrections de bugs.',
            ],
            'major' => [
                'title' => 'Mise à jour recommandée',
                'message' => 'Cette mise à jour apporte des fonctionnalités importantes. Nous vous recommandons de mettre l’application à jour.',
            ],
            'urgent' => [
                'title' => 'Mise à jour obligatoire',
                'message' => 'Une nouvelle version est requise pour continuer à utiliser l’application. Veuillez effectuer la mise à jour.',
            ],
        ];
    }

    public function apiPayload(): array
    {
        return [
            'app_name' => $this->app_name,
            'latest_version_android' => $this->latest_version_android,
            'build_version_android' => (int) $this->build_version_android,
            'play_store_url' => $this->play_store_url,
            'latest_version_ios' => $this->latest_version_ios,
            'build_version_ios' => (int) $this->build_version_ios,
            'app_store_url' => $this->app_store_url,
            'update_type' => $this->update_type,
            'messages' => $this->messages ?: self::defaultMessages(),
        ];
    }
}
