<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstitutionAdminController extends Controller
{
    public function index(): View
    {
        $query = User::query()
            ->with(['organization.application.features', 'organization.featureOverrides', 'features'])
            ->whereNotNull('organization_id');

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', request('organization_id'));
        }

        return view('super-admin.institution-admins.index', [
            'admins' => $query->latest()->paginate(12)->withQueryString(),
            'organizations' => Organization::query()
                ->with(['application.features', 'featureOverrides'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'features' => Feature::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validateRequest($request);
        $organization = Organization::query()
            ->with(['application.features', 'featureOverrides'])
            ->findOrFail($attributes['organization_id']);

        $admin = User::query()->create([
            'organization_id' => $attributes['organization_id'],
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'password' => Hash::make($attributes['password']),
            'is_super_admin' => false,
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        $this->syncInstitutionAdminFeatures($admin, $organization, $attributes['feature_ids'] ?? []);

        return redirect()->route('super-admin.institution-admins.index')
            ->with('success', 'L admin institutionnel a ete cree.');
    }

    public function edit(User $institutionAdmin): View
    {
        return view('super-admin.institution-admins.edit', [
            'institutionAdmin' => $institutionAdmin->load(['organization.application.features', 'organization.featureOverrides', 'features']),
            'organizations' => Organization::query()
                ->with(['application.features', 'featureOverrides'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'features' => Feature::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $institutionAdmin): RedirectResponse
    {
        $attributes = $this->validateRequest($request, $institutionAdmin);
        $organization = Organization::query()
            ->with(['application.features', 'featureOverrides'])
            ->findOrFail($attributes['organization_id']);

        $payload = [
            'organization_id' => $attributes['organization_id'],
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
        ];

        if (filled($attributes['password'] ?? null)) {
            $payload['password'] = Hash::make($attributes['password']);
        }

        $institutionAdmin->update($payload);
        $this->syncInstitutionAdminFeatures($institutionAdmin, $organization, $attributes['feature_ids'] ?? []);

        return redirect()->route('super-admin.institution-admins.index')
            ->with('success', 'L admin institutionnel a ete mis a jour.');
    }

    public function destroy(User $institutionAdmin): RedirectResponse
    {
        $institutionAdmin->delete();

        return redirect()->route('super-admin.institution-admins.index')
            ->with('success', 'L admin institutionnel a ete supprime.');
    }

    public function toggleStatus(User $institutionAdmin): RedirectResponse
    {
        $institutionAdmin->update([
            'status' => $institutionAdmin->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de l admin institutionnel a ete mis a jour.');
    }

    private function validateRequest(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);
    }

    private function allowedFeatureIdsForOrganization(Organization $organization, array $featureIds): array
    {
        $allowedFeatureIds = collect($organization->resolvedFeatureIds())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedFeatureIds = collect($featureIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($allowedFeatureIds->isEmpty()) {
            return $selectedFeatureIds->all();
        }

        return $selectedFeatureIds
            ->filter(fn ($id) => $allowedFeatureIds->contains($id))
            ->values()
            ->all();
    }

    private function syncInstitutionAdminFeatures(User $institutionAdmin, Organization $organization, array $featureIds): void
    {
        $allowedFeatureIds = collect($organization->resolvedFeatureIds())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedFeatureIds = collect($this->allowedFeatureIdsForOrganization($organization, $featureIds))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $inheritsAllOrganizationFeatures = $allowedFeatureIds->isNotEmpty()
            && $selectedFeatureIds->count() === $allowedFeatureIds->count()
            && $selectedFeatureIds->diff($allowedFeatureIds)->isEmpty();

        // No direct assignment means the root AI inherits the organization's
        // full feature perimeter, including future app/org feature updates.
        $institutionAdmin->features()->sync($inheritsAllOrganizationFeatures ? [] : $selectedFeatureIds->all());
    }
}
