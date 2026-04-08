<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('tagline', 255)->nullable();
            $table->string('short_description', 255)->nullable();
            $table->text('long_description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('primary_color', 20)->nullable();
            $table->string('secondary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('application_content_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('page_key', 80)->index();
            $table->string('block_key', 80);
            $table->string('title', 180)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->json('meta')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['application_id', 'page_key', 'block_key'], 'application_content_blocks_unique');
        });

        Schema::table('organizations', function (Blueprint $table): void {
            $table->foreignId('application_id')
                ->nullable()
                ->after('organization_type_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('signal_types', function (Blueprint $table): void {
            $table->foreignId('application_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->foreignId('application_id')
                ->nullable()
                ->after('public_user_id')
                ->constrained()
                ->nullOnDelete();
        });

        $applications = [
            [
                'code' => 'MON_NRJ',
                'name' => 'MON NRJ',
                'slug' => 'mon-nrj',
                'tagline' => 'Les griefs lies a l energie au meme endroit.',
                'short_description' => 'Pour les coupures, surtensions, cables denudes, compteurs et incidents lies a l energie.',
                'long_description' => 'MON NRJ regroupe les griefs des consommateurs lies a l energie afin d organiser le suivi, la reparation des torts subis et, lorsque cela s applique, les demarches de dedommagement.',
                'logo_path' => 'image/logo/logo-my-signal.png',
                'primary_color' => '#0c2435',
                'secondary_color' => '#1e5877',
                'accent_color' => '#cb6f2c',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'code' => 'MON_EAU',
                'name' => 'MON EAU',
                'slug' => 'mon-eau',
                'tagline' => 'Les griefs lies a l eau potable au meme endroit.',
                'short_description' => 'Pour les problemes d acces a l eau potable, de fuite, de pression et de qualite de service.',
                'long_description' => 'MON EAU aide a structurer les signalements des consommateurs sur l eau potable, a suivre les interventions et a documenter les prejudices eventuels.',
                'logo_path' => 'image/logo/logo-my-signal.png',
                'primary_color' => '#0c2435',
                'secondary_color' => '#1e5877',
                'accent_color' => '#cb6f2c',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'code' => 'MA_BANK',
                'name' => 'MA BANK',
                'slug' => 'ma-bank',
                'tagline' => 'Les griefs bancaires et microfinances au meme endroit.',
                'short_description' => 'Pour les litiges bancaires, microfinances, transactions, frais et incidents de services financiers.',
                'long_description' => 'MA BANK centralise les griefs des consommateurs vis-a-vis des banques et institutions de microfinance afin de soutenir les actions correctives et les reparations utiles.',
                'logo_path' => 'image/logo/logo-my-signal.png',
                'primary_color' => '#0c2435',
                'secondary_color' => '#1e5877',
                'accent_color' => '#cb6f2c',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'code' => 'MON_ASS',
                'name' => 'MON ASS',
                'slug' => 'mon-ass',
                'tagline' => 'Les griefs lies aux assurances au meme endroit.',
                'short_description' => 'Pour les litiges d assurances, sinistres, remboursements et qualite de service.',
                'long_description' => 'MON ASS permet de regrouper les griefs des assures, d objectiver les delais et de documenter les suites ou dedommagements eventuels.',
                'logo_path' => 'image/logo/logo-my-signal.png',
                'primary_color' => '#0c2435',
                'secondary_color' => '#1e5877',
                'accent_color' => '#cb6f2c',
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'code' => 'MA_COM',
                'name' => 'MA COM',
                'slug' => 'ma-com',
                'tagline' => 'Les griefs telecoms, voix, SMS et data au meme endroit.',
                'short_description' => 'Pour les incidents de telecommunication, voix, SMS, internet et qualite de connectivite.',
                'long_description' => 'MA COM regroupe les griefs des consommateurs lies aux telecommunications et a la data afin de soutenir la reparation des torts et l amelioration de service.',
                'logo_path' => 'image/logo/logo-my-signal.png',
                'primary_color' => '#0c2435',
                'secondary_color' => '#1e5877',
                'accent_color' => '#cb6f2c',
                'status' => 'active',
                'sort_order' => 5,
            ],
            [
                'code' => 'MON_ENVI',
                'name' => 'MON ENVI',
                'slug' => 'mon-envi',
                'tagline' => 'Les griefs environnementaux au meme endroit.',
                'short_description' => 'Pour les problemes environnementaux, pollutions, nuisances et atteintes au cadre de vie.',
                'long_description' => 'MON ENVI aide a regrouper les griefs des consommateurs et citoyens lies a l environnement afin de mieux documenter les atteintes et soutenir les actions correctives.',
                'logo_path' => 'image/logo/logo-my-signal.png',
                'primary_color' => '#0c2435',
                'secondary_color' => '#1e5877',
                'accent_color' => '#cb6f2c',
                'status' => 'active',
                'sort_order' => 6,
            ],
        ];

        foreach ($applications as $application) {
            DB::table('applications')->updateOrInsert(
                ['code' => $application['code']],
                array_merge($application, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]),
            );
        }

        $applicationIdsByCode = DB::table('applications')->pluck('id', 'code');
        $signalNetworkMap = [
            'CIE' => 'MON_NRJ',
            'SODECI' => 'MON_EAU',
        ];

        foreach ($signalNetworkMap as $networkType => $applicationCode) {
            $applicationId = $applicationIdsByCode[$applicationCode] ?? null;

            if ($applicationId === null) {
                continue;
            }

            DB::table('signal_types')
                ->where('network_type', $networkType)
                ->whereNull('application_id')
                ->update(['application_id' => $applicationId]);

            DB::table('incident_reports')
                ->where('network_type', $networkType)
                ->whereNull('application_id')
                ->update(['application_id' => $applicationId]);
        }
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('application_id');
        });

        Schema::table('signal_types', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('application_id');
        });

        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('application_id');
        });

        Schema::dropIfExists('application_content_blocks');
        Schema::dropIfExists('applications');
    }
};
