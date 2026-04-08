<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Feature;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use InteractsWithInstitutionContext;

    public function edit(Request $request): View
    {
        $context = $this->institutionContext();
        $user = $request->user()->loadMissing(['roles.permissions', 'permissions', 'features', 'organization.application']);
        $permissionCodes = $user->permissions->pluck('code')
            ->merge($user->roles->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
        $featureDetails = Feature::query()
            ->whereIn('code', $context['feature_codes'])
            ->orderBy('name')
            ->get();
        $permissionDetails = Permission::query()
            ->whereIn('code', $permissionCodes)
            ->orderBy('name')
            ->get();

        return view('institution.profile.edit', [
            'organization' => $context['organization'],
            'application' => $context['application'],
            'features' => $context['feature_codes'],
            'activeNav' => 'profile',
            'profileUser' => $user,
            'roleItems' => $user->roles->sortBy('name')->values(),
            'featureDetails' => $featureDetails,
            'permissionDetails' => $permissionDetails,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
        ];

        if (filled($attributes['password'] ?? null)) {
            $payload['password'] = $attributes['password'];
        }

        $user->update($payload);

        return redirect()->route('institution.profile.edit')
            ->with('success', 'Votre profil a ete mis a jour.');
    }
}
