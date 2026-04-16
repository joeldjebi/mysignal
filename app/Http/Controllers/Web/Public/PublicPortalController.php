<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationContentBlock;
use App\Models\BusinessSector;
use App\Models\PublicUserType;
use App\Models\Commune;
use App\Models\LandingPageSection;
use App\Support\ApplicationCatalog;
use Illuminate\Support\Facades\Schema;

class PublicPortalController extends Controller
{
    public function landing()
    {
        return view('public.landing', [
            'applications' => Application::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'publicUserTypes' => PublicUserType::query()
                ->with('pricingRule')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'businessSectors' => BusinessSector::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'communes' => Commune::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'landingBlocks' => ApplicationContentBlock::query()
                ->whereNull('application_id')
                ->where('page_key', 'public_landing')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get()
                ->keyBy('block_key')
                ->merge($this->landingSections()),
        ]);
    }

    public function auth()
    {
        return view('public.auth', [
            'publicUserTypes' => PublicUserType::query()
                ->with('pricingRule')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'businessSectors' => BusinessSector::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'communes' => Commune::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function dashboard()
    {
        $serviceApplications = Application::query()
            ->where('status', 'active')
            ->whereHas('organizations', fn ($query) => $query->where('status', 'active'))
            ->with(['organizations' => fn ($query) => $query
                ->where('status', 'active')
                ->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Application $application) {
                $networkType = ApplicationCatalog::networkTypeForApplicationCode($application->code);

                return [
                    'id' => $application->id,
                    'code' => $application->code,
                    'name' => $application->name,
                    'network_type' => $networkType,
                    'organizations' => $application->organizations->map(fn ($organization) => [
                        'id' => $organization->id,
                        'code' => $organization->code,
                        'name' => $organization->name,
                        'network_type' => $organization->code ?: $networkType,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return view('public.dashboard', [
            'publicUserTypes' => PublicUserType::query()
                ->with('pricingRule')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'businessSectors' => BusinessSector::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'communes' => Commune::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'serviceApplications' => $serviceApplications,
        ]);
    }

    private function landingSections()
    {
        if (! Schema::hasTable('landing_page_sections')) {
            return collect();
        }

        return LandingPageSection::query()
            ->with('items')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(function (LandingPageSection $section): array {
                return [
                    $section->key => (object) [
                        'title' => $section->title,
                        'subtitle' => $section->subtitle,
                        'body' => $section->landingBody(),
                        'status' => $section->is_active ? 'active' : 'inactive',
                        'meta' => $section->landingMeta(),
                    ],
                ];
            });
    }
}
