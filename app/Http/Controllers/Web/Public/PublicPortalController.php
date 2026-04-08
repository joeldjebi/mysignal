<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationContentBlock;
use App\Models\BusinessSector;
use App\Models\PublicUserType;
use App\Models\Commune;
use App\Support\ApplicationCatalog;

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
                ->keyBy('block_key'),
        ]);
    }

    public function dashboard()
    {
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
            'serviceApplications' => Application::query()
                ->with(['organizations' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
                ->where('status', 'active')
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
                            'network_type' => $networkType,
                        ])->values(),
                    ];
                })
                ->values(),
        ]);
    }
}
