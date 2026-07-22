<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Neighborhood;
use App\Models\SubNeighborhood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubNeighborhoodController extends Controller
{
    public function index(): View
    {
        $perPage = min(max((int) request()->integer('per_page', 12), 1), 100);
        $query = SubNeighborhood::query()->with('neighborhood.commune');

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

        if (filled(request('neighborhood_id'))) {
            $query->where('neighborhood_id', request('neighborhood_id'));
        }

        return view('super-admin.sub-neighborhoods.index', [
            'subNeighborhoods' => $query->latest()->paginate($perPage)->withQueryString(),
            'neighborhoods' => Neighborhood::query()->with('commune')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'neighborhood_id' => ['required', 'exists:neighborhoods,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:80', 'unique:sub_neighborhoods,code'],
        ]);

        SubNeighborhood::query()->create([
            'neighborhood_id' => $attributes['neighborhood_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.sub-neighborhoods.index')
            ->with('success', 'Le sous-quartier a été créé.');
    }

    public function edit(SubNeighborhood $subNeighborhood): View
    {
        return view('super-admin.sub-neighborhoods.edit', [
            'subNeighborhood' => $subNeighborhood->load('neighborhood.commune'),
            'neighborhoods' => Neighborhood::query()->with('commune')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SubNeighborhood $subNeighborhood): RedirectResponse
    {
        $attributes = $request->validate([
            'neighborhood_id' => ['required', 'exists:neighborhoods,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:80', 'unique:sub_neighborhoods,code,'.$subNeighborhood->id],
        ]);

        $subNeighborhood->update([
            'neighborhood_id' => $attributes['neighborhood_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
        ]);

        return redirect()->route('super-admin.sub-neighborhoods.index')
            ->with('success', 'Le sous-quartier a été mis à jour.');
    }

    public function destroy(SubNeighborhood $subNeighborhood): RedirectResponse
    {
        $subNeighborhood->delete();

        return redirect()->route('super-admin.sub-neighborhoods.index')
            ->with('success', 'Le sous-quartier a été supprimé.');
    }

    public function toggleStatus(SubNeighborhood $subNeighborhood): RedirectResponse
    {
        $subNeighborhood->update([
            'status' => $subNeighborhood->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du sous-quartier a été mis à jour.');
    }
}
