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
use Illuminate\Support\Str;
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
                    ->orWhere('identifier_label', 'like', '%'.$search.'%')
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
            ->with('success', 'La catégorie a ete creee.');
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
            ->with('success', 'La catégorie a ete mise a jour.');
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
            'name' => ['required', 'string', 'max:120'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'icon_file' => ['nullable', 'file', 'mimes:png,svg,jpg,jpeg', 'max:2048'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'requires_public_user_identifier' => ['nullable', 'boolean'],
            'identifier_label' => ['nullable', 'string', 'max:120'],
            'requires_organization_type_on_report' => ['nullable', 'boolean'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $logoPath = $application?->logo_path;
        $heroImagePath = $application?->hero_image_path;
        $iconPath = $application?->icon_path;

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

        if ($request->hasFile('icon_file') && $wasabiService !== null) {
            if (filled($application?->icon_path) && str_starts_with((string) $application->icon_path, 'applications/')) {
                $wasabiService->deleteFile($application->icon_path);
            }

            $iconPath = $wasabiService->uploadFile(
                $request->file('icon_file'),
                config('wasabi.application_icon_directory', 'applications/icons'),
                'application-icon'
            );
        }

        return [[
            'code' => $this->codeFromName($attributes['name'], $application),
            'name' => $attributes['name'],
            'slug' => $this->slugFromName($attributes['name'], $application),
            'tagline' => $application?->tagline,
            'short_description' => $attributes['short_description'] ?? null,
            'long_description' => $attributes['long_description'] ?? null,
            'logo_path' => $logoPath,
            'hero_image_path' => $heroImagePath,
            'icon_path' => $iconPath,
            'primary_color' => $attributes['primary_color'] ?? null,
            'secondary_color' => $attributes['secondary_color'] ?? null,
            'accent_color' => $attributes['accent_color'] ?? null,
            'sort_order' => $attributes['sort_order'] ?? ($application?->sort_order ?? $this->nextSortOrder()),
            'requires_public_user_identifier' => (bool) ($attributes['requires_public_user_identifier'] ?? false),
            'identifier_label' => ($attributes['identifier_label'] ?? null) ?: 'Identifiant',
            'requires_organization_type_on_report' => (bool) ($attributes['requires_organization_type_on_report'] ?? false),
            'status' => $application?->status ?? 'active',
        ], $attributes['feature_ids'] ?? []];
    }

    private function codeFromName(string $name, ?Application $application = null): string
    {
        $baseCode = Str::limit((string) Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_'), 34, '');

        if ($baseCode === '') {
            $baseCode = 'CATEGORIE';
        }

        $code = $baseCode;
        $suffix = 2;

        while (Application::query()
            ->where('code', $code)
            ->when($application, fn ($query) => $query->whereKeyNot($application->id))
            ->exists()) {
            $suffixText = '_'.$suffix++;
            $code = Str::limit($baseCode, 40 - strlen($suffixText), '').$suffixText;
        }

        return $code;
    }

    private function slugFromName(string $name, ?Application $application = null): string
    {
        $baseSlug = Str::limit(Str::slug($name), 110, '');

        if ($baseSlug === '') {
            $baseSlug = 'categorie';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (Application::query()
            ->where('slug', $slug)
            ->when($application, fn ($query) => $query->whereKeyNot($application->id))
            ->exists()) {
            $suffixText = '-'.$suffix++;
            $slug = Str::limit($baseSlug, 120 - strlen($suffixText), '').$suffixText;
        }

        return $slug;
    }

    private function nextSortOrder(): int
    {
        return ((int) Application::query()->max('sort_order')) + 1;
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
