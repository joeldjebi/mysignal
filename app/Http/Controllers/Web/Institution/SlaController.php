<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\OrganizationTypeSignalSla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $organizationTypeId = $context['organization']?->organization_type_id;

        $query = OrganizationTypeSignalSla::query()->with('organizationType');

        if ($organizationTypeId !== null) {
            $query->where('organization_type_id', $organizationTypeId);
        }

        if ($context['network_type'] !== null) {
            $query->where('network_type', $context['network_type']);
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('institution.sla.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'sla',
            'slaPolicies' => $query->orderBy('network_type')->orderBy('signal_code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $context = $this->institutionContext();
        $organizationTypeId = $context['organization']?->organization_type_id;

        abort_if($organizationTypeId === null, 403);

        $attributes = $request->validate([
            'signal_code' => ['required', 'string', 'max:30'],
            'signal_label' => ['required', 'string', 'max:180'],
            'sla_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
        ]);

        OrganizationTypeSignalSla::query()->create([
            'organization_type_id' => $organizationTypeId,
            'network_type' => $context['network_type'] ?? 'CIE',
            'signal_code' => strtoupper($attributes['signal_code']),
            'signal_label' => $attributes['signal_label'],
            'sla_hours' => $attributes['sla_hours'],
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('institution.sla.index')
            ->with('success', 'La regle SLA a ete creee.');
    }

    public function edit(OrganizationTypeSignalSla $sla): View
    {
        $context = $this->institutionContext();
        $this->authorizeSlaAccess($sla, $context);

        return view('institution.sla.edit', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'sla',
            'slaPolicy' => $sla->load('organizationType'),
        ]);
    }

    public function update(Request $request, OrganizationTypeSignalSla $sla): RedirectResponse
    {
        $context = $this->institutionContext();
        $this->authorizeSlaAccess($sla, $context);

        $attributes = $request->validate([
            'signal_label' => ['required', 'string', 'max:180'],
            'sla_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
        ]);

        $sla->update([
            'signal_label' => $attributes['signal_label'],
            'sla_hours' => $attributes['sla_hours'],
            'description' => $attributes['description'] ?? null,
        ]);

        return redirect()->route('institution.sla.index')
            ->with('success', 'Le referentiel SLA a ete mis a jour.');
    }

    public function toggleStatus(OrganizationTypeSignalSla $sla): RedirectResponse
    {
        $context = $this->institutionContext();
        $this->authorizeSlaAccess($sla, $context);

        $sla->update([
            'status' => $sla->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de la regle SLA a ete mis a jour.');
    }

    private function authorizeSlaAccess(OrganizationTypeSignalSla $sla, array $context): void
    {
        $organization = $context['organization'];
        $organizationTypeId = $organization?->organization_type_id;
        $networkType = $context['network_type'];

        abort_if($organizationTypeId === null, 403);
        abort_unless((int) $sla->organization_type_id === (int) $organizationTypeId, 403);

        if ($networkType !== null) {
            abort_unless($sla->network_type === $networkType, 403);
        }
    }
}
