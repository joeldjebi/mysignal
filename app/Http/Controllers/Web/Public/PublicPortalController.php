<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationContentBlock;
use App\Models\BusinessSector;
use App\Models\Commune;
use App\Models\ContactSubmission;
use App\Models\Country;
use App\Models\IncidentReport;
use App\Models\LandingPageSection;
use App\Models\Organization;
use App\Models\PublicUserType;
use App\Support\ApplicationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            'countries' => Country::query()
                ->where('status', 'active')
                ->with([
                    'cities' => fn ($query) => $query->where('status', 'active')->orderBy('name')->with([
                        'communes' => fn ($communeQuery) => $communeQuery->where('status', 'active')->orderBy('name'),
                    ]),
                ])
                ->orderBy('name')
                ->get(),
            'registrationCountries' => $this->registrationCountries(),
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
                ->toBase()
                ->merge($this->landingSections()),
        ]);
    }

    public function landingPage(string $pageKey)
    {
        $definitions = $this->landingPageDefinitions();

        if (! array_key_exists($pageKey, $definitions)) {
            throw new NotFoundHttpException;
        }

        $landingBlocks = ApplicationContentBlock::query()
            ->whereNull('application_id')
            ->where('page_key', 'public_landing')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get()
            ->keyBy('block_key')
            ->toBase()
            ->merge($this->landingSections());

        $storedPage = $landingBlocks->get($pageKey);

        if ($storedPage && $storedPage->status !== 'active') {
            throw new NotFoundHttpException;
        }

        $defaultPage = (object) ($definitions[$pageKey] + [
            'status' => 'active',
            'meta' => [],
        ]);

        return view('public.landing-page', [
            'landingBlocks' => $landingBlocks,
            'pageKey' => $pageKey,
            'page' => $storedPage ?: $defaultPage,
        ]);
    }

    public function reports()
    {
        $periodStart = now()->subDays(30);
        $baseQuery = IncidentReport::query()
            ->with(['application', 'organization', 'commune'])
            ->where('created_at', '>=', $periodStart);

        $query = clone $baseQuery;

        if (filled(request('search'))) {
            $search = trim((string) request('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('signal_label', 'like', '%'.$search.'%')
                    ->orWhere('signal_code', 'like', '%'.$search.'%')
                    ->orWhere('incident_type', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhereHas('application', fn ($applicationQuery) => $applicationQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('organization', fn ($organizationQuery) => $organizationQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('commune', fn ($communeQuery) => $communeQuery->where('name', 'like', '%'.$search.'%'));
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('application_id'))) {
            $query->where('application_id', request('application_id'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', request('organization_id'));
        }

        if (filled(request('commune_id'))) {
            $query->where('commune_id', request('commune_id'));
        }

        if (request('damage') === 'with') {
            $query->whereNotNull('damage_declared_at');
        }

        if (request('damage') === 'without') {
            $query->whereNull('damage_declared_at');
        }

        $filteredQuery = clone $query;
        $stats = [
            'total' => (clone $filteredQuery)->count(),
            'resolved' => (clone $filteredQuery)->where('status', 'resolved')->count(),
            'in_progress' => (clone $filteredQuery)->where('status', 'in_progress')->count(),
            'damages' => (clone $filteredQuery)->whereNotNull('damage_declared_at')->count(),
            'communes' => (clone $filteredQuery)->whereNotNull('commune_id')->distinct('commune_id')->count('commune_id'),
            'period_start' => $periodStart,
        ];

        return view('public.reports', [
            'reports' => $query->latest()->paginate(15)->withQueryString(),
            'stats' => $stats,
            'applications' => Application::query()
                ->whereIn('id', (clone $baseQuery)->whereNotNull('application_id')->distinct()->select('application_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'organizations' => Organization::query()
                ->whereIn('id', (clone $baseQuery)->whereNotNull('organization_id')->distinct()->select('organization_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'communes' => Commune::query()
                ->whereIn('id', (clone $baseQuery)->whereNotNull('commune_id')->distinct()->select('commune_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'landingBlocks' => ApplicationContentBlock::query()
                ->whereNull('application_id')
                ->where('page_key', 'public_landing')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get()
                ->keyBy('block_key')
                ->toBase()
                ->merge($this->landingSections()),
        ]);
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:60'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        ContactSubmission::query()->create($attributes + [
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return back()->with('success', 'Votre message a bien ete envoye. Notre equipe vous repondra rapidement.');
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
            'countries' => Country::query()
                ->where('status', 'active')
                ->with([
                    'cities' => fn ($query) => $query->where('status', 'active')->orderBy('name')->with([
                        'communes' => fn ($communeQuery) => $communeQuery->where('status', 'active')->orderBy('name'),
                    ]),
                ])
                ->orderBy('name')
                ->get(),
            'registrationCountries' => $this->registrationCountries(),
            'communes' => Commune::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function registrationCountries()
    {
        return Country::query()
            ->where('status', 'active')
            ->with([
                'cities' => fn ($query) => $query->where('status', 'active')->orderBy('name')->with([
                    'communes' => fn ($communeQuery) => $communeQuery->where('status', 'active')->orderBy('name'),
                ]),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Country $country) => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'cities' => $country->cities->map(fn ($city) => [
                    'id' => $city->id,
                    'name' => $city->name,
                    'code' => $city->code,
                    'communes' => $city->communes->map(fn ($commune) => [
                        'id' => $commune->id,
                        'name' => $commune->name,
                        'code' => $commune->code,
                    ])->values(),
                ])->values(),
            ])
            ->values();
    }

    public function dashboard()
    {
        $serviceApplications = Application::query()
            ->where('status', 'active')
            ->whereHas('organizations', fn ($query) => $query->where('status', 'active'))
            ->with(['organizations' => fn ($query) => $query
                ->with('organizationType')
                ->where('status', 'active')
                ->whereHas('organizationType', fn ($typeQuery) => $typeQuery->where('status', 'active'))
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
                    'requires_public_user_identifier' => (bool) $application->requires_public_user_identifier,
                    'requires_organization_type_on_report' => (bool) $application->requires_organization_type_on_report,
                    'organization_types' => $application->organizations
                        ->pluck('organizationType')
                        ->filter()
                        ->unique('id')
                        ->sortBy('name')
                        ->values()
                        ->map(fn ($type) => [
                            'id' => $type->id,
                            'code' => $type->code,
                            'name' => $type->name,
                        ])
                        ->all(),
                    'organizations' => $application->organizations->map(fn ($organization) => [
                        'id' => $organization->id,
                        'code' => $organization->code,
                        'name' => $organization->name,
                        'organization_type_id' => $organization->organization_type_id,
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
                        'body' => $this->decodeRichBody($section->key, $section->landingBody()),
                        'status' => $section->is_active ? 'active' : 'inactive',
                        'meta' => $section->landingMeta(),
                    ],
                ];
            });
    }

    private function landingPageDefinitions(): array
    {
        return [
            'page_about' => [
                'title' => 'Qui sommes-nous ?',
                'subtitle' => 'My-Signal',
                'body' => '<p>My-Signal accompagne les consommateurs, les unites partenaires et les institutions dans le signalement, le suivi et la resolution des difficultes liees aux services du quotidien.</p>',
                'meta' => ['icon' => 'bi-people-fill'],
            ],
            'page_tv' => [
                'title' => 'My-Signal TV',
                'subtitle' => 'Videos et informations',
                'body' => "Retrouvez ici les contenus videos, les campagnes d'information et les annonces importantes autour de My-Signal.",
                'meta' => ['icon' => 'bi-play-btn-fill', 'video_url' => ''],
            ],
            'page_faq' => [
                'title' => 'FAQ',
                'subtitle' => 'Questions frequentes',
                'body' => 'Les reponses aux questions les plus courantes sur le compte UP, les signalements, les notifications, les reductions et les espaces partenaires.',
                'meta' => ['icon' => 'bi-question-circle-fill'],
            ],
            'page_contact' => [
                'title' => 'Contactez-nous',
                'subtitle' => 'Besoin d’aide ?',
                'body' => "L'equipe My-Signal reste disponible pour vous orienter, vous accompagner ou recevoir vos demandes d'information.",
                'meta' => [
                    'icon' => 'bi-envelope-paper-fill',
                    'email' => 'contact@my-signal.online',
                    'phone' => '',
                    'address' => '',
                ],
            ],
            'page_terms' => [
                'title' => 'Conditions generales d utilisation',
                'subtitle' => 'Cadre d utilisation',
                'body' => '<p>Renseignez ici les conditions generales d utilisation de My-Signal.</p>',
                'meta' => ['icon' => 'bi-file-earmark-text-fill'],
            ],
            'page_privacy' => [
                'title' => 'Politique de confidentialite',
                'subtitle' => 'Protection des donnees',
                'body' => '<p>Renseignez ici la politique de confidentialite et de protection des donnees personnelles.</p>',
                'meta' => ['icon' => 'bi-shield-lock-fill'],
            ],
        ];
    }

    private function decodeRichBody(string $key, ?string $body): ?string
    {
        if (! in_array($key, ['page_about', 'page_contact', 'page_tv', 'page_terms', 'page_privacy'], true)) {
            return $body;
        }

        return html_entity_decode((string) $body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
