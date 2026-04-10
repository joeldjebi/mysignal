<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Organization;
use App\Models\SignalType;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SignalTypeController extends Controller
{
    public function index(): View
    {
        $query = SignalType::query()->with(['application', 'organization']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('label', 'like', '%'.$search.'%')
                    ->orWhere('network_type', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('application_id'))) {
            $query->where('application_id', request('application_id'));
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', request('organization_id'));
        }

        return view('super-admin.signal-types.index', [
            'signalTypes' => $query->orderBy('application_id')->orderBy('organization_id')->orderBy('code')->paginate(12)->withQueryString(),
            'applications' => Application::query()
                ->with(['organizations' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'existingSignalTypeCodes' => SignalType::query()->pluck('code')->values(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $signalType = SignalType::query()->create($this->validatedAttributes($request));

        $activityLogger->log(
            'signal_type.created',
            'Creation d un type de signal.',
            $signalType,
            [
                'code' => $signalType->code,
                'label' => $signalType->label,
                'application_id' => $signalType->application_id,
                'organization_id' => $signalType->organization_id,
                'status' => $signalType->status,
            ],
            $request
        );

        return redirect()->route('super-admin.signal-types.index')
            ->with('success', 'Le type de signal a ete creee.');
    }

    public function edit(SignalType $signalType): View
    {
        return view('super-admin.signal-types.edit', [
            'signalType' => $signalType,
            'applications' => Application::query()
                ->with(['organizations' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'existingSignalTypeCodes' => SignalType::query()
                ->where('id', '!=', $signalType->id)
                ->pluck('code')
                ->values(),
        ]);
    }

    public function update(Request $request, SignalType $signalType, ActivityLogger $activityLogger): RedirectResponse
    {
        $before = $signalType->only([
            'code', 'label', 'application_id', 'organization_id', 'default_sla_hours', 'description', 'status', 'data_fields',
        ]);
        $signalType->update($this->validatedAttributes($request, $signalType));

        $activityLogger->log(
            'signal_type.updated',
            'Mise a jour d un type de signal.',
            $signalType,
            [
                'before' => $before,
                'after' => $signalType->only([
                    'code', 'label', 'application_id', 'organization_id', 'default_sla_hours', 'description', 'status', 'data_fields',
                ]),
            ],
            $request
        );

        return redirect()->route('super-admin.signal-types.index')
            ->with('success', 'Le type de signal a ete mis a jour.');
    }

    public function destroy(Request $request, SignalType $signalType, ActivityLogger $activityLogger): RedirectResponse
    {
        $snapshot = $signalType->only(['id', 'code', 'label', 'application_id', 'organization_id', 'status']);
        $signalType->delete();

        $activityLogger->log(
            'signal_type.deleted',
            'Suppression d un type de signal.',
            SignalType::class,
            $snapshot,
            $request
        );

        return redirect()->route('super-admin.signal-types.index')
            ->with('success', 'Le type de signal a ete supprime.');
    }

    public function toggleStatus(Request $request, SignalType $signalType, ActivityLogger $activityLogger): RedirectResponse
    {
        $signalType->update([
            'status' => $signalType->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'signal_type.status_toggled',
            'Changement de statut d un type de signal.',
            $signalType,
            [
                'status' => $signalType->status,
            ],
            $request
        );

        return back()->with('success', 'Le statut du type de signal a ete mis a jour.');
    }

    private function validatedAttributes(Request $request, ?SignalType $signalType = null): array
    {
        $attributes = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('signal_types', 'code')
                    ->where(function ($query) use ($request) {
                        $applicationId = (int) $request->input('application_id');
                        $organizationId = $request->filled('organization_id') ? (int) $request->input('organization_id') : null;

                        $query->where('application_id', $applicationId);

                        if ($organizationId === null) {
                            $query->whereNull('organization_id');
                        } else {
                            $query->where('organization_id', $organizationId);
                        }
                    })
                    ->ignore($signalType?->id),
            ],
            'label' => ['required', 'string', 'max:180'],
            'default_sla_hours' => ['nullable', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
            'field_keys' => ['nullable', 'array'],
            'field_keys.*' => ['nullable', 'string', 'max:80'],
            'field_labels' => ['nullable', 'array'],
            'field_labels.*' => ['nullable', 'string', 'max:180'],
            'field_types' => ['nullable', 'array'],
            'field_types.*' => ['nullable', 'in:text,number,textarea,select'],
            'field_options' => ['nullable', 'array'],
            'field_options.*' => ['nullable', 'string'],
            'field_required' => ['nullable', 'array'],
        ]);

        $application = Application::query()->whereKey($attributes['application_id'])->where('status', 'active')->firstOrFail();
        $organization = null;

        if (! empty($attributes['organization_id'])) {
            $organization = Organization::query()
                ->whereKey($attributes['organization_id'])
                ->where('application_id', $application->id)
                ->where('status', 'active')
                ->first();

            if ($organization === null) {
                throw ValidationException::withMessages([
                    'organization_id' => ['L organisation selectionnee n appartient pas a l application choisie.'],
                ]);
            }
        }

        return [
            'application_id' => $application->id,
            'organization_id' => $organization?->id,
            'network_type' => strtoupper((string) ($organization?->code ?: $application->code)),
            'code' => strtoupper($attributes['code']),
            'label' => $attributes['label'],
            'default_sla_hours' => $attributes['default_sla_hours'] ?? null,
            'description' => $attributes['description'] ?? null,
            'data_fields' => $this->normalizeDataFields(
                $attributes['field_keys'] ?? [],
                $attributes['field_labels'] ?? [],
                $attributes['field_types'] ?? [],
                $attributes['field_options'] ?? [],
                $attributes['field_required'] ?? [],
            ),
        ];
    }

    private function normalizeDataFields(array $keys, array $labels, array $types, array $options, array $requiredFlags): array
    {
        $rows = [];

        foreach ($keys as $index => $key) {
            $normalizedKey = trim((string) $key);
            $normalizedLabel = trim((string) ($labels[$index] ?? ''));
            $normalizedType = (string) ($types[$index] ?? 'text');
            $normalizedOptions = collect(preg_split('/\r\n|\r|\n/', (string) ($options[$index] ?? '')))
                ->map(fn ($option) => trim((string) $option))
                ->filter()
                ->values()
                ->all();

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
                'type' => in_array($normalizedType, ['text', 'number', 'textarea', 'select'], true) ? $normalizedType : 'text',
                'required' => in_array((string) $index, array_map('strval', $requiredFlags), true),
                'options' => $normalizedType === 'select' ? $normalizedOptions : [],
            ];

            if ($normalizedType === 'select' && count($normalizedOptions) === 0) {
                throw ValidationException::withMessages([
                    'field_options' => ['Chaque champ de type liste doit contenir au moins une option.'],
                ]);
            }
        }

        return $rows;
    }
}
