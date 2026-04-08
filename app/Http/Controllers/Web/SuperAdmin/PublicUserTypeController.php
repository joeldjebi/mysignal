<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Models\PublicUserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicUserTypeController extends Controller
{
    public function index(): View
    {
        $query = PublicUserType::query()->with('pricingRule');

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

        return view('super-admin.public-user-types.index', [
            'publicUserTypes' => $query->orderBy('sort_order')->orderBy('name')->paginate(12)->withQueryString(),
            'pricingRules' => PricingRule::query()->orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PublicUserType::query()->create($this->validatedPayload($request));

        return redirect()->route('super-admin.public-user-types.index')
            ->with('success', 'Le type d usager public a ete cree.');
    }

    public function edit(PublicUserType $publicUserType): View
    {
        return view('super-admin.public-user-types.edit', [
            'publicUserType' => $publicUserType->load('pricingRule'),
            'pricingRules' => PricingRule::query()->orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, PublicUserType $publicUserType): RedirectResponse
    {
        $publicUserType->update($this->validatedPayload($request, $publicUserType));

        return redirect()->route('super-admin.public-user-types.index')
            ->with('success', 'Le type d usager public a ete mis a jour.');
    }

    public function destroy(PublicUserType $publicUserType): RedirectResponse
    {
        $publicUserType->delete();

        return redirect()->route('super-admin.public-user-types.index')
            ->with('success', 'Le type d usager public a ete supprime.');
    }

    public function toggleStatus(PublicUserType $publicUserType): RedirectResponse
    {
        $publicUserType->update([
            'status' => $publicUserType->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du type d usager public a ete mis a jour.');
    }

    private function validatedPayload(Request $request, ?PublicUserType $publicUserType = null): array
    {
        $attributes = $request->validate([
            'pricing_rule_id' => ['required', 'exists:pricing_rules,id'],
            'code' => ['required', 'string', 'max:60', Rule::unique('public_user_types', 'code')->ignore($publicUserType?->id)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'profile_kind' => ['required', 'string', Rule::in(['individual', 'business'])],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        return [
            'pricing_rule_id' => $attributes['pricing_rule_id'],
            'code' => strtoupper((string) $attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'profile_kind' => $attributes['profile_kind'],
            'sort_order' => $attributes['sort_order'] ?? 1,
            'status' => $publicUserType?->status ?? 'active',
        ];
    }
}
