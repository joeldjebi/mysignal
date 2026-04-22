<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Feature;
use App\Services\WasabiService;
use App\Support\Audit\ActivityLogger;
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

    public function store(Request $request, ActivityLogger $activityLogger, WasabiService $wasabiService): RedirectResponse
    {
        [$attributes, $featureIds] = $this->validatedPayload($request, null, $wasabiService);
        $application = Application::query()->create($attributes);
        $application->features()->sync($featureIds);

        $activityLogger->log(
            'application.created',
            'Creation d une application.',
            $application,
            [
                'code' => $application->code,
                'name' => $application->name,
                'status' => $application->status,
                'feature_ids' => $featureIds,
            ],
            $request
        );

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

    public function update(Request $request, Application $application, ActivityLogger $activityLogger, WasabiService $wasabiService): RedirectResponse
    {
        [$attributes, $featureIds] = $this->validatedPayload($request, $application, $wasabiService);
        $before = $application->load('features');
        $application->update($attributes);
        $application->features()->sync($featureIds);

        $activityLogger->log(
            'application.updated',
            'Mise a jour d une application.',
            $application,
            [
                'before' => [
                    'code' => $before->code,
                    'name' => $before->name,
                    'slug' => $before->slug,
                    'status' => $before->status,
                    'feature_ids' => $before->features->pluck('id')->all(),
                ],
                'after' => [
                    'code' => $application->code,
                    'name' => $application->name,
                    'slug' => $application->slug,
                    'status' => $application->status,
                    'feature_ids' => $featureIds,
                ],
            ],
            $request
        );

        return redirect()->route('super-admin.applications.index')
            ->with('success', 'L application a ete mise a jour.');
    }

    public function destroy(Request $request, Application $application, ActivityLogger $activityLogger): RedirectResponse
    {
        $snapshot = $application->only(['id', 'code', 'name', 'slug', 'status']);
        $application->delete();

        $activityLogger->log(
            'application.deleted',
            'Suppression d une application.',
            Application::class,
            $snapshot,
            $request
        );

        return redirect()->route('super-admin.applications.index')
            ->with('success', 'L application a ete supprimee.');
    }

    public function toggleStatus(Request $request, Application $application, ActivityLogger $activityLogger): RedirectResponse
    {
        $application->update([
            'status' => $application->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'application.status_toggled',
            'Changement de statut d une application.',
            $application,
            [
                'status' => $application->status,
            ],
            $request
        );

        return back()->with('success', 'Le statut de l application a ete mis a jour.');
    }

    private function validatedPayload(Request $request, ?Application $application = null, ?WasabiService $wasabiService = null): array
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:applications,code,'.($application?->id ?? 'NULL')],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'unique:applications,slug,'.($application?->id ?? 'NULL')],
            'tagline' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $logoPath = $application?->logo_path;
        $heroImagePath = $application?->hero_image_path;

        if ($request->hasFile('logo_file') && $wasabiService !== null) {
            if (filled($application?->logo_path) && str_starts_with((string) $application->logo_path, 'applications/')) {
                $wasabiService->deleteFile($application->logo_path);
            }

            $logoPath = $wasabiService->uploadFile(
                $request->file('logo_file'),
                config('wasabi.application_logo_directory', 'applications/logos'),
                'application-logo'
            );
        }

        if ($request->hasFile('hero_image_file') && $wasabiService !== null) {
            if (filled($application?->hero_image_path) && str_starts_with((string) $application->hero_image_path, 'applications/')) {
                $wasabiService->deleteFile($application->hero_image_path);
            }

            $heroImagePath = $wasabiService->uploadFile(
                $request->file('hero_image_file'),
                config('wasabi.application_hero_directory', 'applications/heroes'),
                'application-hero'
            );
        }

        return [[
            'code' => strtoupper((string) $attributes['code']),
            'name' => $attributes['name'],
            'slug' => strtolower((string) $attributes['slug']),
            'tagline' => $attributes['tagline'] ?? null,
            'short_description' => $attributes['short_description'] ?? null,
            'long_description' => $attributes['long_description'] ?? null,
            'logo_path' => $logoPath,
            'hero_image_path' => $heroImagePath,
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
