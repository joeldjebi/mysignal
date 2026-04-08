<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\SignalType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SignalTypeController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $query = SignalType::query()->with(['application', 'organization']);

        if ($context['application_id'] !== null) {
            $query->where('application_id', $context['application_id']);
        }

        if ($context['organization_id'] !== null) {
            $query->where(function ($builder) use ($context): void {
                $builder->whereNull('organization_id')
                    ->orWhere('organization_id', $context['organization_id']);
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('institution.signal-types.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'signal-types',
            'signalTypes' => $query->orderByRaw('CASE WHEN organization_id IS NULL THEN 0 ELSE 1 END')->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $context = $this->institutionContext();
        abort_if($context['organization_id'] === null, 403);

        SignalType::query()->create($this->validatedAttributes($request, $context['organization_id'], $context['application_id']));

        return redirect()->route('institution.signal-types.index')
            ->with('success', 'Le type de signal a ete creee.');
    }

    public function edit(SignalType $signalType): View
    {
        $context = $this->institutionContext();
        $this->authorizeSignalTypeAccess($signalType, $context);

        return view('institution.signal-types.edit', [
            'organization' => $context['organization'],
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
            ->with('success', 'Le type de signal a ete mis a jour.');
    }

    public function toggleStatus(SignalType $signalType): RedirectResponse
    {
        $context = $this->institutionContext();
        $this->authorizeSignalTypeAccess($signalType, $context);

        $signalType->update([
            'status' => $signalType->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du type de signal a ete mis a jour.');
    }

    private function authorizeSignalTypeAccess(SignalType $signalType, array $context): void
    {
        abort_if($context['organization_id'] === null, 403);
        abort_if($context['application_id'] !== null && (int) $signalType->application_id !== (int) $context['application_id'], 403);
        abort_if($signalType->organization_id === null, 403);
        abort_unless((int) $signalType->organization_id === (int) $context['organization_id'], 403);
    }

    private function validatedAttributes(Request $request, int $organizationId, ?int $applicationId = null, bool $updating = false): array
    {
        $attributes = $request->validate([
            'code' => [
                $updating ? 'sometimes' : 'required',
                'string',
                'max:30',
                Rule::unique('signal_types', 'code')
                    ->where(fn ($query) => $query->where('organization_id', $organizationId))
                    ->ignore($request->route('signalType')?->id),
            ],
            'label' => ['required', 'string', 'max:180'],
            'default_sla_hours' => ['nullable', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
            'field_keys' => ['nullable', 'array'],
            'field_keys.*' => ['nullable', 'string', 'max:80'],
            'field_labels' => ['nullable', 'array'],
            'field_labels.*' => ['nullable', 'string', 'max:180'],
            'field_types' => ['nullable', 'array'],
            'field_types.*' => ['nullable', 'in:text,number,textarea'],
            'field_required' => ['nullable', 'array'],
        ]);

        return array_filter([
            'application_id' => $applicationId,
            'organization_id' => $organizationId,
            'network_type' => strtoupper((string) $request->user()?->organization?->code),
            'code' => array_key_exists('code', $attributes) ? strtoupper($attributes['code']) : null,
            'label' => $attributes['label'],
            'default_sla_hours' => $attributes['default_sla_hours'] ?? null,
            'description' => $attributes['description'] ?? null,
            'data_fields' => $this->normalizeDataFields(
                $attributes['field_keys'] ?? [],
                $attributes['field_labels'] ?? [],
                $attributes['field_types'] ?? [],
                $attributes['field_required'] ?? [],
            ),
        ], fn ($value, $key) => ! ($key === 'code' && $value === null), ARRAY_FILTER_USE_BOTH);
    }

    private function normalizeDataFields(array $keys, array $labels, array $types, array $requiredFlags): array
    {
        $rows = [];

        foreach ($keys as $index => $key) {
            $normalizedKey = trim((string) $key);
            $normalizedLabel = trim((string) ($labels[$index] ?? ''));
            $normalizedType = (string) ($types[$index] ?? 'text');

            if ($normalizedKey === '' && $normalizedLabel === '') {
                continue;
            }

            if ($normalizedKey === '' || $normalizedLabel === '') {
                throw ValidationException::withMessages([
                    'field_keys' => ['Chaque champ requis doit avoir une cle et un libelle.'],
                ]);
            }

            $rows[] = [
                'key' => $normalizedKey,
                'label' => $normalizedLabel,
                'type' => in_array($normalizedType, ['text', 'number', 'textarea'], true) ? $normalizedType : 'text',
                'required' => in_array((string) $index, array_map('strval', $requiredFlags), true),
            ];
        }

        return $rows;
    }
}
