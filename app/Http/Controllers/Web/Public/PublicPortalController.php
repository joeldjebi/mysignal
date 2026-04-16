<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationContentBlock;
use App\Models\BusinessSector;
use App\Models\PublicUserType;
use App\Models\Commune;
use App\Support\ApplicationCatalog;
use Illuminate\Http\Response;

class PublicPortalController extends Controller
{
    public function landing()
    {
        $customLandingPage = ApplicationContentBlock::query()
            ->whereNull('application_id')
            ->where('page_key', 'public_landing')
            ->where('block_key', 'custom_page')
            ->where('status', 'active')
            ->first();

        if ($customLandingPage && filled($customLandingPage->body)) {
            return $this->customLandingResponse($customLandingPage);
        }

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

    private function customLandingResponse(ApplicationContentBlock $landingPage): Response
    {
        $html = strtr($landingPage->body, [
            '{{ logo_url }}' => asset('image/logo/logo-my-signal.png'),
            '{{ app_name }}' => config('app.name', 'MySignal'),
            '{{ primary_color }}' => $landingPage->meta['primary_color'] ?? '#183447',
            '{{ secondary_color }}' => $landingPage->meta['secondary_color'] ?? '#256f8f',
            '{{ accent_color }}' => $landingPage->meta['accent_color'] ?? '#ff0068',
        ]);

        if (! str_contains(strtolower($html), '<html')) {
            $title = e($landingPage->title ?: config('app.name', 'MySignal'));
            $primaryColor = e($landingPage->meta['primary_color'] ?? '#183447');
            $secondaryColor = e($landingPage->meta['secondary_color'] ?? '#256f8f');
            $accentColor = e($landingPage->meta['accent_color'] ?? '#ff0068');

            $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        :root {
            --primary: {$primaryColor};
            --secondary: {$secondaryColor};
            --accent: {$accentColor};
        }
    </style>
</head>
<body>
{$html}
</body>
</html>
HTML;
        }

        return response($html);
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
}
