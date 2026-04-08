<?php

namespace Database\Seeders\Reference;

use App\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
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
            Application::query()->updateOrCreate(
                ['code' => $application['code']],
                $application,
            );
        }
    }
}
