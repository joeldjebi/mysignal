<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('institution_activation_letter_settings')) {
            return;
        }

        $logoPath = 'public:image/logo/ufc.jpg';
        $headerSettings = [
            'logo_width' => 150,
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
                'text' => 'DE CÔTE D’IVOIRE',
                'size' => 17,
                'color' => '#101828',
                'bold' => true,
                'italic' => false,
            ],
            'description' => [
                'text' => 'UFC - Côte d’Ivoire • Association de défense des consommateurs',
                'size' => 11,
                'color' => '#667085',
                'bold' => false,
                'italic' => false,
            ],
        ];
        $footerSettings = [
            'logo' => [
                'label' => '',
                'size' => 58,
            ],
            'address' => [
                'label' => 'Adresse',
                'text' => "Marcory, Biétry, Résidence BIMBOIS\n09 BP 1099 Abidjan 09\nAbidjan, Côte d’Ivoire",
                'size' => 8,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
            'phone' => [
                'label' => 'Téléphone',
                'text' => "0700007006\n0708083356\n27 21 24 24 92",
                'size' => 8,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
            'email' => [
                'label' => 'Email',
                'text' => 'hello@my-signal.pro',
                'size' => 8,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
            'website' => [
                'label' => 'Site web',
                'text' => 'https://my-signal.pro',
                'size' => 8,
                'color' => '#475467',
                'bold' => false,
                'italic' => false,
            ],
        ];

        DB::table('institution_activation_letter_settings')->updateOrInsert(
            ['key' => 'default'],
            [
                'logo_position' => 'left',
                'logo_path' => $logoPath,
                'signature_name' => 'L’équipe My-Signal',
                'signature_title' => 'Union Fédérale des consommateurs de Côte d’Ivoire',
                'signature_content' => '<p>Cordialement,<br><strong>L’équipe My-Signal</strong></p>',
                'footer_logo_path' => $logoPath,
                'header_settings' => json_encode($headerSettings),
                'footer_settings' => json_encode($footerSettings),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if (! Schema::hasTable('institution_activation_letters')) {
            return;
        }

        DB::table('institution_activation_letters')
            ->whereIn('status', ['draft', 'generated'])
            ->update([
                'logo_position' => 'left',
                'logo_path' => $logoPath,
                'signature_name' => 'L’équipe My-Signal',
                'signature_title' => 'Union Fédérale des consommateurs de Côte d’Ivoire',
                'signature_content' => '<p>Cordialement,<br><strong>L’équipe My-Signal</strong></p>',
                'footer_logo_path' => $logoPath,
                'header_settings' => json_encode($headerSettings),
                'footer_settings' => json_encode($footerSettings),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
