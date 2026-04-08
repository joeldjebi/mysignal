<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessSectorController extends Controller
{
    public function index(): View
    {
        $query = BusinessSector::query();

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

        return view('super-admin.business-sectors.index', [
            'businessSectors' => $query->orderBy('sort_order')->orderBy('name')->paginate(12)->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);

        BusinessSector::query()->create([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'sort_order' => $attributes['sort_order'] ?? 0,
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.business-sectors.index')
            ->with('success', 'Le secteur d activite a ete cree.');
    }

    public function edit(BusinessSector $businessSector): View
    {
        return view('super-admin.business-sectors.edit', [
            'businessSector' => $businessSector,
        ]);
    }

    public function update(Request $request, BusinessSector $businessSector): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request, $businessSector);

        $businessSector->update([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'sort_order' => $attributes['sort_order'] ?? 0,
        ]);

        return redirect()->route('super-admin.business-sectors.index')
            ->with('success', 'Le secteur d activite a ete mis a jour.');
    }

    public function destroy(BusinessSector $businessSector): RedirectResponse
    {
        $businessSector->delete();

        return redirect()->route('super-admin.business-sectors.index')
            ->with('success', 'Le secteur d activite a ete supprime.');
    }

    public function toggleStatus(BusinessSector $businessSector): RedirectResponse
    {
        $businessSector->update([
            'status' => $businessSector->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du secteur d activite a ete mis a jour.');
    }

    private function validatedAttributes(Request $request, ?BusinessSector $businessSector = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:business_sectors,code,'.($businessSector?->id ?? 'NULL').',id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }
}
