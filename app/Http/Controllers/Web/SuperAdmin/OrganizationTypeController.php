<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationTypeController extends Controller
{
    public function index(): View
    {
        $query = OrganizationType::query();

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('super-admin.client-types.index', [
            'organizationTypes' => $query->latest()->paginate(12)->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:organization_types,code'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        OrganizationType::query()->create([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.client-types.index')
            ->with('success', 'Le type de client a ete cree.');
    }

    public function edit(OrganizationType $clientType): View
    {
        return view('super-admin.client-types.edit', [
            'organizationType' => $clientType,
        ]);
    }

    public function update(Request $request, OrganizationType $clientType): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:organization_types,code,'.$clientType->id],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $clientType->update([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
        ]);

        return redirect()->route('super-admin.client-types.index')
            ->with('success', 'Le type de client a ete mis a jour.');
    }

    public function destroy(OrganizationType $clientType): RedirectResponse
    {
        $clientType->delete();

        return redirect()->route('super-admin.client-types.index')
            ->with('success', 'Le type de client a ete supprime.');
    }

    public function toggleStatus(OrganizationType $clientType): RedirectResponse
    {
        $clientType->update([
            'status' => $clientType->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du type de client a ete mis a jour.');
    }
}
