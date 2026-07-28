<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\SignalType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SignalTypeController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $query = $this->institutionSignalTypesQuery($context['application_id'], $context['organization_id']);

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('institution.signal-types.index', [
            'organization' => $context['organization'],
            'application' => $context['application'],
            'features' => $context['feature_codes'],
            'activeNav' => 'signal-types',
            'signalTypes' => $query->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $context = $this->institutionContext();
        abort_if($context['organization_id'] === null, 403);

        SignalType::query()->create($this->validatedAttributes($request, $context['organization_id'], $context['application_id']));

        return redirect()->route('institution.signal-types.index')
            ->with('success', 'Le type de signal a été créé.');
    }

    public function edit(SignalType $signalType): View
    {
        $context = $this->institutionContext();
        $this->authorizeSignalTypeAccess($signalType, $context);

        return view('institution.signal-types.edit', [
            'organization' => $context['organization'],
            'application' => $context['application'],
            'features' => $context['feature_codes'],
            'activeNav' => 'signal-types',
            'signalType' => $signalType,
        ]);
    }

    public function update(Request $request, SignalType $signalType): RedirectResponse
    {
        $context = $this->institutionContext();
        $this->authorizeSignalTypeAccess($signalType, $context);

        $signalType->update($this->validatedAttributes($request, $context['organization_id'], $context['application_id'], true));

        return redirect()->route('institution.signal-types.index')
            ->with('success', 'Le type de signal a été mis à jour.');
    }

    public function toggleStatus(SignalType $signalType): RedirectResponse
    {
        $context = $this->institutionContext();
        $this->authorizeSignalTypeAccess($signalType, $context);

        $signalType->update([
            'status' => $signalType->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du type de signal a été mis à jour.');
    }

    private function authorizeSignalTypeAccess(SignalType $signalType, array $context): void
    {
        abort_if($context['organization_id'] === null, 403);
        abort_if($context['application_id'] !== null && (int) $signalType->application_id !== (int) $context['application_id'], 403);
        abort_unless((int) $signalType->organization_id === (int) $context['organization_id'], 403);
    }

    private function institutionSignalTypesQuery(?int $applicationId, ?int $organizationId)
    {
        return SignalType::query()
            ->with(['application', 'organization'])
            ->where('application_id', $applicationId)
            ->where('organization_id', $organizationId);
    }

    private function validatedAttributes(Request $request, int $organizationId, ?int $applicationId = null, bool $updating = false): array
    {
        $attributes = $request->validate([
            'code' => [
                $updating ? 'sometimes' : 'nullable',
                'string',
                'max:30',
                Rule::unique('signal_types', 'code')
                    ->where(fn ($query) => $query
                        ->where('application_id', $applicationId)
                        ->where('organization_id', $organizationId))
                    ->ignore($request->route('signalType')?->id),
            ],
            'label' => ['required', 'string', 'max:180'],
            'default_sla_hours' => ['nullable', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
        ]);

        $code = array_key_exists('code', $attributes) && filled($attributes['code'])
            ? strtoupper($attributes['code'])
            : ($updating ? null : $this->generateSignalCode($attributes['label'], $applicationId, $organizationId));

        return array_filter([
            'application_id' => $applicationId,
            'organization_id' => $organizationId,
            'network_type' => strtoupper((string) $request->user()?->organization?->code),
            'code' => $code,
            'label' => $attributes['label'],
            'default_sla_hours' => $attributes['default_sla_hours'] ?? null,
            'description' => $attributes['description'] ?? null,
        ], fn ($value, $key) => ! ($key === 'code' && $value === null), ARRAY_FILTER_USE_BOTH);
    }

    private function generateSignalCode(string $label, ?int $applicationId, int $organizationId): string
    {
        $base = strtoupper(Str::slug($label, '_')) ?: 'SIGNAL';
        $base = Str::limit($base, 24, '');
        $candidate = $base;
        $index = 1;

        while (SignalType::query()
            ->where('application_id', $applicationId)
            ->where('organization_id', $organizationId)
            ->where('code', $candidate)
            ->exists()) {
            $suffix = '_'.str_pad((string) $index, 2, '0', STR_PAD_LEFT);
            $candidate = Str::limit($base, 30 - strlen($suffix), '').$suffix;
            $index++;
        }

        return $candidate;
    }
}
