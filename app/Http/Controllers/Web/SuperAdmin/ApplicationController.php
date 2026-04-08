<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $query = Application::query()->with(['features'])->withCount(['organizations', 'signalTypes', 'features']);
        $features = Feature::query()->where('status', 'active')->orderBy('name')->get();

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('tagline', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('super-admin.applications.index', [
            'applications' => $query->orderBy('sort_order')->orderBy('name')->paginate(12)->withQueryString(),
            'features' => $features,
            'groupedFeatures' => $this->groupFeatures($features),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$attributes, $featureIds] = $this->validatedPayload($request);
        $application = Application::query()->create($attributes);
        $application->features()->sync($featureIds);

        return redirect()->route('super-admin.applications.index')
            ->with('success', 'L application a ete creee.');
    }

    public function edit(Application $application): View
    {
        $features = Feature::query()->where('status', 'active')->orderBy('name')->get();

        return view('super-admin.applications.edit', [
            'application' => $application->load(['features'])->loadCount(['organizations', 'signalTypes', 'incidentReports', 'features']),
            'features' => $features,
            'groupedFeatures' => $this->groupFeatures($features),
        ]);
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        [$attributes, $featureIds] = $this->validatedPayload($request, $application);
        $application->update($attributes);
        $application->features()->sync($featureIds);

        return redirect()->route('super-admin.applications.index')
            ->with('success', 'L application a ete mise a jour.');
    }

    public function destroy(Application $application): RedirectResponse
    {
        $application->delete();

        return redirect()->route('super-admin.applications.index')
            ->with('success', 'L application a ete supprimee.');
    }

    public function toggleStatus(Application $application): RedirectResponse
    {
        $application->update([
            'status' => $application->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de l application a ete mis a jour.');
    }

    private function validatedPayload(Request $request, ?Application $application = null): array
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:applications,code,'.($application?->id ?? 'NULL')],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'unique:applications,slug,'.($application?->id ?? 'NULL')],
            'tagline' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'hero_image_path' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        return [[
            'code' => strtoupper((string) $attributes['code']),
            'name' => $attributes['name'],
            'slug' => strtolower((string) $attributes['slug']),
            'tagline' => $attributes['tagline'] ?? null,
            'short_description' => $attributes['short_description'] ?? null,
            'long_description' => $attributes['long_description'] ?? null,
            'logo_path' => $attributes['logo_path'] ?? null,
            'hero_image_path' => $attributes['hero_image_path'] ?? null,
            'primary_color' => $attributes['primary_color'] ?? null,
            'secondary_color' => $attributes['secondary_color'] ?? null,
            'accent_color' => $attributes['accent_color'] ?? null,
            'sort_order' => $attributes['sort_order'] ?? 1,
            'status' => $application?->status ?? 'active',
        ], $attributes['feature_ids'] ?? []];
    }

    private function groupFeatures(Collection $features): Collection
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
}
