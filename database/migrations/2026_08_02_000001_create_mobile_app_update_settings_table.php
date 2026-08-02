<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_update_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('app_name')->unique();
            $table->string('latest_version_android', 30);
            $table->unsignedInteger('build_version_android')->default(1);
            $table->string('play_store_url')->nullable();
            $table->string('latest_version_ios', 30);
            $table->unsignedInteger('build_version_ios')->default(1);
            $table->string('app_store_url')->nullable();
            $table->string('update_type', 20)->default('minor');
            $table->json('messages');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['app_name', 'status']);
        });

        DB::table('mobile_app_update_settings')->insert([
            'app_name' => 'mysignal',
            'latest_version_android' => '1.0.5',
            'build_version_android' => 5,
            'play_store_url' => 'https://play.google.com/store/apps/details?id=com.bwan.mysignal',
            'latest_version_ios' => '1.0.5',
            'build_version_ios' => 5,
            'app_store_url' => 'https://apps.apple.com/ci/app/my-signal/id6764384980',
            'update_type' => 'minor',
            'messages' => json_encode($this->defaultMessages(), JSON_UNESCAPED_UNICODE),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_update_settings');
    }

    private function defaultMessages(): array
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
};
