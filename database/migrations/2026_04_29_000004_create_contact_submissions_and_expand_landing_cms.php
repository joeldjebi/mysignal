<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('email', 180);
            $table->string('phone', 60)->nullable();
            $table->string('subject', 180)->nullable();
            $table->text('message');
            $table->string('ip_address', 80)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });

        if (Schema::hasTable('landing_page_section_items')) {
            DB::statement('ALTER TABLE landing_page_section_items ALTER COLUMN url TYPE TEXT');
        }

        if (! Schema::hasTable('landing_page_sections')) {
            return;
        }

        $now = now();

        foreach ([
            'page_terms' => [
                'label' => 'Page - Conditions generales d utilisation',
                'title' => 'Conditions generales d utilisation',
                'subtitle' => 'Cadre d utilisation',
                'body' => '<p>Renseignez ici les conditions generales d utilisation de My-Signal.</p>',
                'sort_order' => 17,
            ],
            'page_privacy' => [
                'label' => 'Page - Politique de confidentialite',
                'title' => 'Politique de confidentialite',
                'subtitle' => 'Protection des donnees',
                'body' => '<p>Renseignez ici la politique de confidentialite et de protection des donnees personnelles.</p>',
                'sort_order' => 18,
            ],
        ] as $key => $page) {
            DB::table('landing_page_sections')->updateOrInsert(
                ['key' => $key],
                [
                    'label' => $page['label'],
                    'title' => $page['title'],
                    'subtitle' => $page['subtitle'],
                    'body' => $page['body'],
                    'meta' => json_encode(['icon' => $key === 'page_terms' ? 'bi-file-earmark-text-fill' : 'bi-shield-lock-fill']),
                    'is_active' => true,
                    'sort_order' => $page['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $footer = DB::table('landing_page_sections')->where('key', 'footer')->first();

        if (! $footer || ! Schema::hasTable('landing_page_section_items')) {
            return;
        }

        DB::table('landing_page_section_items')
            ->where('landing_page_section_id', $footer->id)
            ->where('item_key', 'column_3_links')
            ->delete();

        foreach ([
            ['Conditions generales d utilisation', '/conditions-generales-utilisation'],
            ['Politique de confidentialite', '/politique-confidentialite'],
            ['Contact', '/contactez-nous'],
        ] as $index => [$title, $url]) {
            DB::table('landing_page_section_items')->insert([
                'landing_page_section_id' => $footer->id,
                'item_key' => 'column_3_links',
                'title' => $title,
                'url' => $url,
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');

        if (Schema::hasTable('landing_page_sections')) {
            DB::table('landing_page_sections')
                ->whereIn('key', ['page_terms', 'page_privacy'])
                ->delete();
        }
    }
};
