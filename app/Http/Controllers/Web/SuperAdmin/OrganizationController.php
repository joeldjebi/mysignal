<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Feature;
use App\Models\IncidentReport;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        $query = Organization::query()->with(['application.features', 'organizationType', 'featureOverrides']);
        $features = Feature::query()->where('status', 'active')->orderBy('name')->get();

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('portal_key', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('application_id'))) {
            $query->where('application_id', request('application_id'));
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('organization_type_id'))) {
            $query->where('organization_type_id', request('organization_type_id'));
        }

        return view('super-admin.organizations.index', [
            'organizations' => $query->latest()->paginate(12)->withQueryString(),
            'applications' => Application::query()->with('features')->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'organizationTypes' => OrganizationType::query()->where('status', 'active')->orderBy('name')->get(),
            'features' => $features,
            'groupedFeatures' => $this->groupFeatures($features),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'application_id' => ['nullable', 'exists:applications,id'],
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'code' => ['required', 'string', 'max:60', 'unique:organizations,code'],
            'name' => ['required', 'string', 'max:180'],
            'portal_key' => ['nullable', 'string', 'max:60', 'unique:organizations,portal_key'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $organization = Organization::query()->create([
            'application_id' => $attributes['application_id'] ?? null,
            'organization_type_id' => $attributes['organization_type_id'],
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'portal_key' => filled($attributes['portal_key'] ?? null) ? strtolower($attributes['portal_key']) : null,
            'email' => $attributes['email'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        $organization->loadMissing('application.features');
        $this->syncOrganizationFeatures($organization, $attributes['feature_ids'] ?? []);

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'L organisation a ete creee.');
    }

    public function edit(Organization $organization): View
    {
        $features = Feature::query()->where('status', 'active')->orderBy('name')->get();

        return view('super-admin.organizations.edit', [
            'organization' => $organization->load(['application.features', 'organizationType', 'featureOverrides']),
            'applications' => Application::query()->with('features')->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'organizationTypes' => OrganizationType::query()->where('status', 'active')->orderBy('name')->get(),
            'features' => $features,
            'groupedFeatures' => $this->groupFeatures($features),
        ]);
    }

    public function show(Organization $organization): View
    {
        $organization->load([
            'application.features',
            'organizationType',
            'featureOverrides',
        ]);

        $organizationId = $organization->id;

        $stats = [
            'admins_count' => User::query()->where('organization_id', $organizationId)->count(),
            'meters_count' => \App\Models\Meter::query()->where('organization_id', $organizationId)->count(),
            'reports_count' => IncidentReport::query()->where('organization_id', $organizationId)->count(),
            'resolved_reports_count' => IncidentReport::query()->where('organization_id', $organizationId)->where('status', 'resolved')->count(),
            'open_reports_count' => IncidentReport::query()->where('organization_id', $organizationId)->whereIn('status', ['submitted', 'in_progress'])->count(),
            'damages_count' => IncidentReport::query()->where('organization_id', $organizationId)->whereNotNull('damage_declared_at')->count(),
            'payments_count' => Payment::query()->whereHas('incidentReport', fn ($query) => $query->where('organization_id', $organizationId))->count(),
            'payments_total' => (float) Payment::query()->whereHas('incidentReport', fn ($query) => $query->where('organization_id', $organizationId))->where('status', 'confirmed')->sum('amount'),
        ];

        $recentAdmins = User::query()
            ->where('organization_id', $organizationId)
            ->latest()
            ->take(6)
            ->get();

        $recentReports = IncidentReport::query()
            ->with(['publicUser', 'meter', 'commune'])
            ->where('organization_id', $organizationId)
            ->latest()
            ->take(6)
            ->get();

        $reportStatusBreakdown = IncidentReport::query()
            ->selectRaw('status, count(*) as aggregate')
            ->where('organization_id', $organizationId)
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $damageStatusBreakdown = IncidentReport::query()
            ->selectRaw("coalesce(damage_resolution_status, 'non_soumis') as status, count(*) as aggregate")
            ->where('organization_id', $organizationId)
            ->groupByRaw("coalesce(damage_resolution_status, 'non_soumis')")
            ->pluck('aggregate', 'status');

        return view('super-admin.organizations.show', [
            'organization' => $organization,
            'stats' => $stats,
            'recentAdmins' => $recentAdmins,
            'recentReports' => $recentReports,
            'reportStatusBreakdown' => $reportStatusBreakdown,
            'damageStatusBreakdown' => $damageStatusBreakdown,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $attributes = $request->validate([
            'application_id' => ['nullable', 'exists:applications,id'],
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'code' => ['required', 'string', 'max:60', 'unique:organizations,code,'.$organization->id],
            'name' => ['required', 'string', 'max:180'],
            'portal_key' => ['nullable', 'string', 'max:60', 'unique:organizations,portal_key,'.$organization->id],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $organization->update([
            'application_id' => $attributes['application_id'] ?? null,
            'organization_type_id' => $attributes['organization_type_id'],
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'portal_key' => filled($attributes['portal_key'] ?? null) ? strtolower($attributes['portal_key']) : null,
            'email' => $attributes['email'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'description' => $attributes['description'] ?? null,
        ]);

        $organization->loadMissing('application.features');
        $this->syncOrganizationFeatures($organization, $attributes['feature_ids'] ?? []);

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'L organisation a ete mise a jour.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $organization->delete();

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'L organisation a ete supprimee.');
    }

    public function toggleStatus(Organization $organization): RedirectResponse
    {
        $organization->update([
            'status' => $organization->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de l organisation a ete mis a jour.');
    }

    private function groupFeatures($features)
    {
        return $features
            ->groupBy(function (Feature $feature): string {
                return match (true) {
                    str_starts_with($feature->code, 'INSTITUTION_DASHBOARD_') => 'Dashboard institutionnel',
                    str_starts_with($feature->code, 'INSTITUTION_') => 'Acces institutionnels',
                    str_starts_with($feature->code, 'PUBLIC_') => 'Modules publics',
                    default => 'Autres fonctionnalites',
                };
            })
            ->sortKeys();
    }

    private function syncOrganizationFeatures(Organization $organization, array $selectedFeatureIds): void
    {
        $selectedIds = collect($selectedFeatureIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $applicationFeatureIds = collect($organization->application?->features?->pluck('id')->all() ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $enabledOverrideIds = $selectedIds
            ->diff($applicationFeatureIds)
            ->values();

        $disabledOverrideIds = $applicationFeatureIds
            ->diff($selectedIds)
            ->values();

        $payload = $enabledOverrideIds
            ->mapWithKeys(fn (int $featureId) => [$featureId => ['enabled' => true]])
            ->all();

        foreach ($disabledOverrideIds as $featureId) {
            $payload[(int) $featureId] = ['enabled' => false];
        }

        $organization->featureOverrides()->sync($payload);
    }
}
