<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\OrganizationTypeSignalSla;
use App\Models\SignalType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SlaController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $organizationTypeId = $context['organization']?->organization_type_id;
        $signalTypes = $this->scopedSignalTypes($context);
        $signalCodes = $signalTypes->pluck('code')->unique()->values();

        $query = OrganizationTypeSignalSla::query()->with('organizationType');

        if ($organizationTypeId !== null) {
            $query->where('organization_type_id', $organizationTypeId);
        }

        $signalCodes->isEmpty()
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('signal_code', $signalCodes->all());

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
            'slaPolicies' => $query->orderBy('network_type')->orderBy('signal_code')->paginate(15)->withQueryString(),
            'signalTypes' => $signalTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $context = $this->institutionContext();
        $organizationTypeId = $context['organization']?->organization_type_id;

        abort_if($organizationTypeId === null, 403);

        $attributes = $request->validate([
            'signal_code' => ['required', 'string', 'max:30'],
            'sla_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
        ]);

        $signalType = $this->scopedSignalTypes($context)
            ->firstWhere('code', strtoupper($attributes['signal_code']));

        abort_if($signalType === null, 403);

        OrganizationTypeSignalSla::query()->updateOrCreate(
            [
                'organization_type_id' => $organizationTypeId,
                'signal_code' => $signalType->code,
            ],
            [
                'network_type' => $signalType->organization?->code
                    ?: $signalType->application?->code
                    ?: $signalType->network_type
                    ?: ($context['network_type'] ?? 'GENERAL'),
                'signal_label' => $signalType->label,
                'sla_hours' => $attributes['sla_hours'],
                'description' => $attributes['description'] ?? $signalType->description,
                'status' => 'active',
            ],
        );

        return redirect()->route('institution.sla.index')
            ->with('success', 'La règle TCM a été créée.');
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
            ->with('success', 'Le référentiel TCM a été mis à jour.');
    }

    public function toggleStatus(OrganizationTypeSignalSla $sla): RedirectResponse
    {
        $context = $this->institutionContext();
        $this->authorizeSlaAccess($sla, $context);

        $sla->update([
            'status' => $sla->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de la règle TCM a été mis à jour.');
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

        $allowedSignalCodes = $this->scopedSignalTypes($context)->pluck('code')->all();
        abort_unless(in_array($sla->signal_code, $allowedSignalCodes, true), 403);
    }

    private function scopedSignalTypes(array $context): Collection
    {
        return SignalType::query()
            ->with(['application:id,code,name', 'organization:id,code,name'])
            ->where('status', 'active')
            ->when($context['application_id'] !== null, fn ($query) => $query->where('application_id', $context['application_id']))
            ->when($context['organization_id'] !== null, fn ($query) => $query->where('organization_id', $context['organization_id']))
            ->when($context['network_type'] !== null, fn ($query) => $query->where('network_type', $context['network_type']))
            ->orderBy('code')
            ->get();
    }
}
