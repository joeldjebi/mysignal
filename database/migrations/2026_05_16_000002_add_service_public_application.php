<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CODE = 'SERVICE_PUBLIC';

    public function up(): void
    {
        DB::table('applications')->updateOrInsert(
            ['code' => self::CODE],
            [
                'name' => 'Service Public',
                'slug' => 'service-public',
                'tagline' => 'Les griefs lies aux services publics au meme endroit.',
                'short_description' => 'Pour les difficultes administratives, lenteurs de traitement, acces aux services publics et qualite d accueil.',
                'long_description' => 'Service Public centralise les griefs des citoyens et usagers lies aux services publics afin de documenter les dysfonctionnements, suivre leur traitement et soutenir l amelioration continue.',
                'logo_path' => 'image/logo/logo-my-signal.png',
                'primary_color' => '#0c2435',
                'secondary_color' => '#1e5877',
                'accent_color' => '#2f9e6d',
                'status' => 'active',
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('applications')
            ->where('code', self::CODE)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('organizations')
                    ->whereColumn('organizations.application_id', 'applications.id');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('signal_types')
                    ->whereColumn('signal_types.application_id', 'applications.id');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('incident_reports')
                    ->whereColumn('incident_reports.application_id', 'applications.id');
            })
            ->delete();
    }
};
