<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Commune;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommuneController extends Controller
{
    public function index(): View
    {
        $query = Commune::query()->with('city.country');

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

        if (filled(request('city_id'))) {
            $query->where('city_id', request('city_id'));
        }

        return view('super-admin.communes.index', [
            'communes' => $query->latest()->paginate(12)->withQueryString(),
            'cities' => City::query()->with('country')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:communes,code'],
        ]);

        Commune::query()->create([
            'city_id' => $attributes['city_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.communes.index')
            ->with('success', 'La commune a ete creee.');
    }

    public function edit(Commune $commune): View
    {
        return view('super-admin.communes.edit', [
            'commune' => $commune->load('city.country'),
            'cities' => City::query()->with('country')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Commune $commune): RedirectResponse
    {
        $attributes = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:communes,code,'.$commune->id],
        ]);

        $commune->update([
            'city_id' => $attributes['city_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
        ]);

        return redirect()->route('super-admin.communes.index')
            ->with('success', 'La commune a ete mise a jour.');
    }

    public function destroy(Commune $commune): RedirectResponse
    {
        $commune->delete();

        return redirect()->route('super-admin.communes.index')
            ->with('success', 'La commune a ete supprimee.');
    }

    public function toggleStatus(Commune $commune): RedirectResponse
    {
        $commune->update([
            'status' => $commune->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de la commune a ete mis a jour.');
    }
}
