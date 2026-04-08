<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function index(): View
    {
        $query = Feature::query();

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

        return view('super-admin.features.index', [
            'features' => $query->latest()->paginate(12)->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:features,code'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        Feature::query()->create([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.features.index')
            ->with('success', 'La fonctionnalite a ete creee.');
    }

    public function edit(Feature $feature): View
    {
        return view('super-admin.features.edit', [
            'feature' => $feature,
        ]);
    }

    public function update(Request $request, Feature $feature): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:features,code,'.$feature->id],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $feature->update([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
        ]);

        return redirect()->route('super-admin.features.index')
            ->with('success', 'La fonctionnalite a ete mise a jour.');
    }

    public function destroy(Feature $feature): RedirectResponse
    {
        $feature->delete();

        return redirect()->route('super-admin.features.index')
            ->with('success', 'La fonctionnalite a ete supprimee.');
    }

    public function toggleStatus(Feature $feature): RedirectResponse
    {
        $feature->update([
            'status' => $feature->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de la fonctionnalite a ete mis a jour.');
    }
}
