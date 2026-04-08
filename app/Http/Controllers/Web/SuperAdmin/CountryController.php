<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $query = Country::query();

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('dial_code', 'like', '%'.$search.'%')
                    ->orWhere('flag', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('super-admin.countries.index', [
            'countries' => $query
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:10', 'unique:countries,code'],
            'dial_code' => ['required', 'string', 'regex:/^[0-9]{1,4}$/'],
            'flag' => ['required', 'string', 'max:20'],
        ]);

        Country::query()->create([
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
            'dial_code' => $attributes['dial_code'],
            'flag' => $attributes['flag'],
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.countries.index')
            ->with('success', 'Le pays a ete cree.');
    }

    public function edit(Country $country): View
    {
        return view('super-admin.countries.edit', [
            'country' => $country,
        ]);
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:10', 'unique:countries,code,'.$country->id],
            'dial_code' => ['required', 'string', 'regex:/^[0-9]{1,4}$/'],
            'flag' => ['required', 'string', 'max:20'],
        ]);

        $country->update([
            'name' => $attributes['name'],
            'code' => strtoupper($attributes['code']),
            'dial_code' => $attributes['dial_code'],
            'flag' => $attributes['flag'],
        ]);

        return redirect()->route('super-admin.countries.index')
            ->with('success', 'Le pays a ete mis a jour.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->route('super-admin.countries.index')
            ->with('success', 'Le pays a ete supprime.');
    }

    public function toggleStatus(Country $country): RedirectResponse
    {
        $country->update([
            'status' => $country->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du pays a ete mis a jour.');
    }
}
