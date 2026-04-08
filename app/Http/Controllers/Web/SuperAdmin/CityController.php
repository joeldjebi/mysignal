<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(): View
    {
        $query = City::query()->with('country');

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

        if (filled(request('country_id'))) {
            $query->where('country_id', request('country_id'));
        }

        return view('super-admin.cities.index', [
            'cities' => $query->latest()->paginate(12)->withQueryString(),
            'countries' => Country::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:cities,code'],
        ]);

        City::query()->create([
            'country_id' => $attributes['country_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.cities.index')
            ->with('success', 'La ville a ete creee.');
    }

    public function edit(City $city): View
    {
        return view('super-admin.cities.edit', [
            'city' => $city->load('country'),
            'countries' => Country::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $attributes = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:cities,code,'.$city->id],
        ]);

        $city->update([
            'country_id' => $attributes['country_id'],
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
        ]);

        return redirect()->route('super-admin.cities.index')
            ->with('success', 'La ville a ete mise a jour.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();

        return redirect()->route('super-admin.cities.index')
            ->with('success', 'La ville a ete supprimee.');
    }

    public function toggleStatus(City $city): RedirectResponse
    {
        $city->update([
            'status' => $city->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de la ville a ete mis a jour.');
    }
}
