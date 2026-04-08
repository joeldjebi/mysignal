<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationType;
use App\Models\OrganizationTypeSignalSla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationTypeSignalSlaController extends Controller
{
    public function index(): View
    {
        $query = OrganizationTypeSignalSla::query()->with('organizationType');

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('signal_code', 'like', '%'.$search.'%')
                    ->orWhere('signal_label', 'like', '%'.$search.'%')
                    ->orWhere('network_type', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('organization_type_id'))) {
            $query->where('organization_type_id', request('organization_type_id'));
        }

        if (filled(request('network_type'))) {
            $query->where('network_type', request('network_type'));
        }

        $networkTypes = OrganizationTypeSignalSla::query()
            ->select('network_type')
            ->whereNotNull('network_type')
            ->distinct()
            ->orderBy('network_type')
            ->pluck('network_type');

        return view('super-admin.sla-policies.index', [
            'slaPolicies' => $query->orderBy('organization_type_id')->orderBy('network_type')->orderBy('signal_code')->paginate(12)->withQueryString(),
            'organizationTypes' => OrganizationType::query()->where('status', 'active')->orderBy('name')->get(),
            'networkTypes' => $networkTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validateRequest($request);

        OrganizationTypeSignalSla::query()->create([
            ...$attributes,
            'network_type' => strtoupper($attributes['network_type']),
            'signal_code' => strtoupper($attributes['signal_code']),
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.sla-policies.index')
            ->with('success', 'La regle SLA a ete creee.');
    }

    public function edit(OrganizationTypeSignalSla $slaPolicy): View
    {
        $networkTypes = OrganizationTypeSignalSla::query()
            ->select('network_type')
            ->whereNotNull('network_type')
            ->distinct()
            ->orderBy('network_type')
            ->pluck('network_type')
            ->push($slaPolicy->network_type)
            ->filter()
            ->unique()
            ->values();

        return view('super-admin.sla-policies.edit', [
            'slaPolicy' => $slaPolicy->load('organizationType'),
            'organizationTypes' => OrganizationType::query()->where('status', 'active')->orderBy('name')->get(),
            'networkTypes' => $networkTypes,
        ]);
    }

    public function update(Request $request, OrganizationTypeSignalSla $slaPolicy): RedirectResponse
    {
        $attributes = $this->validateRequest($request);

        $slaPolicy->update([
            ...$attributes,
            'network_type' => strtoupper($attributes['network_type']),
            'signal_code' => strtoupper($attributes['signal_code']),
        ]);

        return redirect()->route('super-admin.sla-policies.index')
            ->with('success', 'La regle SLA a ete mise a jour.');
    }

    public function destroy(OrganizationTypeSignalSla $slaPolicy): RedirectResponse
    {
        $slaPolicy->delete();

        return redirect()->route('super-admin.sla-policies.index')
            ->with('success', 'La regle SLA a ete supprimee.');
    }

    public function toggleStatus(OrganizationTypeSignalSla $slaPolicy): RedirectResponse
    {
        $slaPolicy->update([
            'status' => $slaPolicy->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de la regle SLA a ete mis a jour.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'network_type' => ['required', 'string', 'max:60'],
            'signal_code' => ['required', 'string', 'max:30'],
            'signal_label' => ['required', 'string', 'max:180'],
            'sla_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
