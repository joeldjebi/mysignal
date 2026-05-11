<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('landing_page_sections')) {
            return;
        }

        $now = now();

        $pages = [
            'page_about' => [
                'label' => 'Page - Qui sommes-nous ?',
                'title' => 'Qui sommes-nous ?',
                'subtitle' => 'My-Signal',
                'body' => "My-Signal accompagne les consommateurs, les unites partenaires et les institutions dans le signalement, le suivi et la resolution des difficultes liees aux services du quotidien.",
                'meta' => ['icon' => 'bi-people-fill'],
                'sort_order' => 11,
            ],
            'page_tv' => [
                'label' => 'Page - My-Signal TV',
                'title' => 'My-Signal TV',
                'subtitle' => 'Videos et informations',
                'body' => "Retrouvez ici les contenus videos, les campagnes d'information et les annonces importantes autour de My-Signal.",
                'meta' => ['icon' => 'bi-play-btn-fill', 'video_url' => ''],
                'sort_order' => 14,
            ],
            'page_faq' => [
                'label' => 'Page - FAQ',
                'title' => 'FAQ',
                'subtitle' => 'Questions frequentes',
                'body' => "Les reponses aux questions les plus courantes sur le compte UP, les signalements, les notifications, les reductions et les espaces partenaires.",
                'meta' => ['icon' => 'bi-question-circle-fill'],
                'sort_order' => 15,
            ],
            'page_contact' => [
                'label' => 'Page - Contactez-nous',
                'title' => 'Contactez-nous',
                'subtitle' => 'Besoin d aide ?',
                'body' => "L'equipe My-Signal reste disponible pour vous orienter, vous accompagner ou recevoir vos demandes d'information.",
                'meta' => ['icon' => 'bi-envelope-paper-fill', 'email' => 'contact@my-signal.online', 'phone' => '', 'address' => ''],
                'sort_order' => 16,
            ],
        ];

        foreach ($pages as $key => $page) {
            $existing = DB::table('landing_page_sections')->where('key', $key)->first();

            if ($existing) {
                DB::table('landing_page_sections')
                    ->where('key', $key)
                    ->update([
                        'label' => $page['label'],
                        'sort_order' => $page['sort_order'],
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('landing_page_sections')->insert([
                'key' => $key,
                'label' => $page['label'],
                'title' => $page['title'],
                'subtitle' => $page['subtitle'],
                'body' => $page['body'],
                'meta' => json_encode($page['meta']),
                'is_active' => true,
                'sort_order' => $page['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $navigation = DB::table('landing_page_sections')->where('key', 'navigation')->first();

        if (! $navigation) {
            $navigationId = DB::table('landing_page_sections')->insertGetId([
                'key' => 'navigation',
                'label' => 'Menu principal',
                'title' => 'My-Signal',
                'subtitle' => 'Liens affiches dans le menu',
                'body' => null,
                'meta' => json_encode(['cta_label' => 'Se connecter et signaler maintenant']),
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $navigationId = $navigation->id;
        }

        if (! Schema::hasTable('landing_page_section_items')) {
            return;
        }

        $labels = DB::table('landing_page_section_items')
            ->where('landing_page_section_id', $navigationId)
            ->where('item_key', 'links')
            ->pluck('title')
            ->map(fn ($title) => (string) $title)
            ->all();

        if (in_array('My-Signal TV', $labels, true)) {
            return;
        }

        DB::table('landing_page_section_items')
            ->where('landing_page_section_id', $navigationId)
            ->where('item_key', 'links')
            ->delete();

        foreach ([
            ['Accueil', '/'],
            ['Qui sommes-nous ?', '/qui-sommes-nous'],
            ['Nos domaines', '/#domains'],
            ['Fonctionnalites', '/#features'],
            ['My-Signal TV', '/my-signal-tv'],
            ['FAQ', '/faq'],
            ['Contactez-nous', '/contactez-nous'],
        ] as $index => [$title, $url]) {
            DB::table('landing_page_section_items')->insert([
                'landing_page_section_id' => $navigationId,
                'item_key' => 'links',
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
        if (! Schema::hasTable('landing_page_sections')) {
            return;
        }

        DB::table('landing_page_sections')
            ->whereIn('key', ['page_about', 'page_tv', 'page_faq', 'page_contact'])
            ->delete();
    }
};
