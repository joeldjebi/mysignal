<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationTypeController extends Controller
{
    public function index(): View
    {
        $perPage = min(max((int) request()->integer('per_page', 12), 1), 100);
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
            'organizationTypes' => $query->latest()->paginate($perPage)->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        OrganizationType::query()->create([
            'code' => $this->codeFromName($attributes['name']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.client-types.index')
            ->with('success', 'La sous catégorie a ete creee.');
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
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $clientType->update([
            'code' => $this->codeFromName($attributes['name'], $clientType),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
        ]);

        return redirect()->route('super-admin.client-types.index')
            ->with('success', 'La sous catégorie a ete mise a jour.');
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

    private function codeFromName(string $name, ?OrganizationType $organizationType = null): string
    {
        $baseCode = Str::limit((string) Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_'), 54, '');

        if ($baseCode === '') {
            $baseCode = 'SOUS_CATEGORIE';
        }

        $code = $baseCode;
        $suffix = 2;

        while (OrganizationType::query()
            ->where('code', $code)
            ->when($organizationType, fn ($query) => $query->whereKeyNot($organizationType->id))
            ->exists()) {
            $suffixText = '_'.$suffix++;
            $code = Str::limit($baseCode, 60 - strlen($suffixText), '').$suffixText;
        }

        return $code;
    }
}
