<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Neighborhood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NeighborhoodController extends Controller
{
    public function index(): View
    {
        $perPage = min(max((int) request()->integer('per_page', 12), 1), 100);
        $query = Neighborhood::query()->with('commune.city');

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('commune_id'))) {
            $query->where('commune_id', request('commune_id'));
        }

        return view('super-admin.neighborhoods.index', [
            'neighborhoods' => $query->latest()->paginate($perPage)->withQueryString(),
            'communes' => Commune::query()->with('city')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'commune_id' => ['required', 'exists:communes,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:60', 'unique:neighborhoods,code'],
        ]);

        Neighborhood::query()->create([
            'commune_id' => $attributes['commune_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.neighborhoods.index')
            ->with('success', 'Le quartier a ete cree.');
    }

    public function edit(Neighborhood $neighborhood): View
    {
        return view('super-admin.neighborhoods.edit', [
            'neighborhood' => $neighborhood->load('commune.city'),
            'communes' => Commune::query()->with('city')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Neighborhood $neighborhood): RedirectResponse
    {
        $attributes = $request->validate([
            'commune_id' => ['required', 'exists:communes,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:60', 'unique:neighborhoods,code,'.$neighborhood->id],
        ]);

        $neighborhood->update([
            'commune_id' => $attributes['commune_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
        ]);

        return redirect()->route('super-admin.neighborhoods.index')
            ->with('success', 'Le quartier a ete mis a jour.');
    }

    public function destroy(Neighborhood $neighborhood): RedirectResponse
    {
        $neighborhood->delete();

        return redirect()->route('super-admin.neighborhoods.index')
            ->with('success', 'Le quartier a ete supprime.');
    }

    public function toggleStatus(Neighborhood $neighborhood): RedirectResponse
    {
        $neighborhood->update([
            'status' => $neighborhood->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du quartier a ete mis a jour.');
    }
}
