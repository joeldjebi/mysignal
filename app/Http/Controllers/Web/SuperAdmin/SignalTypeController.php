<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Organization;
use App\Models\SignalSubType;
use App\Models\SignalType;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SignalTypeController extends Controller
{
    public function index(): View
    {
        $query = SignalType::query()->with(['application', 'organization', 'organizations']);

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
            $organizationId = (int) request('organization_id');
            $query->where(function ($query) use ($organizationId): void {
                $query->where('organization_id', $organizationId)
                    ->orWhereHas('organizations', fn ($organizationQuery) => $organizationQuery->whereKey($organizationId));
            });
        }

        return view('super-admin.signal-types.index', [
            'signalTypes' => $query->orderBy('application_id')->orderBy('organization_id')->orderBy('code')->paginate(12)->withQueryString(),
            'applications' => Application::query()
                ->with(['organizations' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);
        $organizationIds = $attributes['organization_ids'];
        unset($attributes['organization_ids']);

        $signalType = SignalType::query()->create($attributes);
        $signalType->organizations()->sync($organizationIds);

        $activityLogger->log(
            'signal_type.created',
            'Creation d un type de signal.',
            $signalType,
            [
                'code' => $signalType->code,
                'label' => $signalType->label,
                'application_id' => $signalType->application_id,
                'organization_ids' => $organizationIds,
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
            'signalType' => $signalType->load([
                'organizations',
                'subTypes' => fn ($query) => $query->orderBy('sort_order')->orderBy('label'),
            ]),
            'applications' => Application::query()
                ->with(['organizations' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function subTypesIndex(): View
    {
        $query = SignalSubType::query()
            ->with(['signalType.application', 'signalType.organization']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('label', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('signalType', function ($signalTypeQuery) use ($search): void {
                        $signalTypeQuery->where('code', 'like', '%'.$search.'%')
                            ->orWhere('label', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('signal_type_id'))) {
            $query->where('signal_type_id', request('signal_type_id'));
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('super-admin.signal-sub-types.index', [
            'subTypes' => $query
                ->join('signal_types', 'signal_sub_types.signal_type_id', '=', 'signal_types.id')
                ->orderBy('signal_types.application_id')
                ->orderBy('signal_types.organization_id')
                ->orderBy('signal_types.code')
                ->orderBy('signal_sub_types.sort_order')
                ->orderBy('signal_sub_types.label')
                ->select('signal_sub_types.*')
                ->paginate(15)
                ->withQueryString(),
            'signalTypes' => SignalType::query()
                ->with(['application', 'organization', 'organizations'])
                ->orderBy('application_id')
                ->orderBy('organization_id')
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function update(Request $request, SignalType $signalType, ActivityLogger $activityLogger): RedirectResponse
    {
        $before = $signalType->only([
            'code', 'label', 'application_id', 'organization_id', 'default_sla_hours', 'description', 'status',
        ]);
        $attributes = $this->validatedAttributes($request, $signalType);
        $organizationIds = $attributes['organization_ids'];
        unset($attributes['organization_ids']);

        $signalType->update($attributes);
        $signalType->organizations()->sync($organizationIds);

        $activityLogger->log(
            'signal_type.updated',
            'Mise a jour d un type de signal.',
            $signalType,
            [
                'before' => $before,
                'after' => $signalType->only([
                    'code', 'label', 'application_id', 'organization_id', 'default_sla_hours', 'description', 'status',
                ]),
                'organization_ids' => $organizationIds,
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

    public function storeSubType(Request $request, SignalType $signalType, ActivityLogger $activityLogger): RedirectResponse
    {
        $subType = $signalType->subTypes()->create($this->validatedSubTypeAttributes($request, $signalType));

        $this->logSubTypeCreation($activityLogger, $request, $signalType, $subType);

        return redirect()->route('super-admin.signal-types.edit', $signalType)
            ->with('success', 'Le sous-type de signal a ete cree.');
    }

    public function storeSubTypeFromIndex(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'signal_type_id' => ['required', 'exists:signal_types,id'],
        ]);

        $signalType = SignalType::query()->findOrFail($attributes['signal_type_id']);
        $subType = $signalType->subTypes()->create($this->validatedSubTypeAttributes($request, $signalType));

        $this->logSubTypeCreation($activityLogger, $request, $signalType, $subType);

        return redirect()->route('super-admin.signal-sub-types.index', ['signal_type_id' => $signalType->id])
            ->with('success', 'Le sous-type de signal a ete cree.');
    }

    public function updateSubType(Request $request, SignalType $signalType, SignalSubType $subType, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->ensureSubTypeBelongsToSignalType($signalType, $subType);

        $before = $subType->only(['code', 'label', 'description', 'sort_order', 'status']);
        $subType->update($this->validatedSubTypeAttributes($request, $signalType, $subType));

        $activityLogger->log(
            'signal_sub_type.updated',
            'Mise a jour d un sous-type de signal.',
            $subType,
            [
                'before' => $before,
                'after' => $subType->only(['code', 'label', 'description', 'sort_order', 'status']),
            ],
            $request
        );

        return redirect()->route('super-admin.signal-types.edit', $signalType)
            ->with('success', 'Le sous-type de signal a ete mis a jour.');
    }

    public function toggleSubTypeStatus(Request $request, SignalType $signalType, SignalSubType $subType, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->ensureSubTypeBelongsToSignalType($signalType, $subType);

        $subType->update([
            'status' => $subType->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'signal_sub_type.status_toggled',
            'Changement de statut d un sous-type de signal.',
            $subType,
            ['status' => $subType->status],
            $request
        );

        return back()->with('success', 'Le statut du sous-type de signal a ete mis a jour.');
    }

    public function destroySubType(Request $request, SignalType $signalType, SignalSubType $subType, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->ensureSubTypeBelongsToSignalType($signalType, $subType);

        $snapshot = $subType->only(['id', 'signal_type_id', 'code', 'label', 'status']);
        $subType->delete();

        $activityLogger->log(
            'signal_sub_type.deleted',
            'Suppression d un sous-type de signal.',
            SignalSubType::class,
            $snapshot,
            $request
        );

        return redirect()->route('super-admin.signal-types.edit', $signalType)
            ->with('success', 'Le sous-type de signal a ete supprime.');
    }

    private function validatedAttributes(Request $request, ?SignalType $signalType = null): array
    {
        $attributes = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', 'exists:organizations,id'],
            'label' => ['required', 'string', 'max:180'],
            'default_sla_hours' => ['nullable', 'integer', 'min:1', 'max:999'],
            'description' => ['nullable', 'string'],
        ]);

        $application = Application::query()->whereKey($attributes['application_id'])->where('status', 'active')->firstOrFail();
        $organizationIds = collect($attributes['organization_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $organizations = collect();

        if ($organizationIds->isNotEmpty()) {
            $organizations = Organization::query()
                ->whereIn('id', $organizationIds)
                ->where('application_id', $application->id)
                ->where('status', 'active')
                ->get();

            if ($organizations->count() !== $organizationIds->count()) {
                throw ValidationException::withMessages([
                    'organization_ids' => ['Une ou plusieurs institutions selectionnees n appartiennent pas a la catégorie choisie.'],
                ]);
            }
        }

        $legacyOrganization = $organizations->count() === 1 ? $organizations->first() : null;

        return [
            'application_id' => $application->id,
            'organization_id' => $legacyOrganization?->id,
            'organization_ids' => $organizationIds->all(),
            'network_type' => strtoupper((string) ($legacyOrganization?->code ?: $application->code)),
            'code' => $this->uniqueSignalTypeCode($application, $signalType),
            'label' => $attributes['label'],
            'default_sla_hours' => $attributes['default_sla_hours'] ?? null,
            'description' => $attributes['description'] ?? null,
        ];
    }

    private function validatedSubTypeAttributes(Request $request, SignalType $signalType, ?SignalSubType $subType = null): array
    {
        $attributes = $request->validate([
            'label' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        return [
            'code' => $this->uniqueSignalSubTypeCode($signalType, $attributes['label'], $subType),
            'label' => $attributes['label'],
            'description' => $attributes['description'] ?? null,
            'sort_order' => $subType?->sort_order ?? $this->nextSubTypeSortOrder($signalType),
            'status' => $subType?->status ?? 'active',
        ];
    }

    private function uniqueSignalTypeCode(Application $application, ?SignalType $signalType = null): string
    {
        $base = collect([$application->code])
            ->filter()
            ->map(fn (string $part): string => $this->codePart($part))
            ->filter()
            ->implode('_') ?: 'SIGNAL';
        $sequence = 1;

        do {
            $suffix = '_'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $candidate = Str::limit($base, 30 - strlen($suffix), '').$suffix;
            $exists = SignalType::query()
                ->where('application_id', $application->id)
                ->where('code', $candidate)
                ->when($signalType, fn ($query) => $query->whereKeyNot($signalType->id))
                ->exists();
            $sequence++;
        } while ($exists);

        return $candidate;
    }

    private function uniqueSignalSubTypeCode(SignalType $signalType, string $label, ?SignalSubType $subType = null): string
    {
        $base = Str::of($label)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_')
            ->limit(50, '')
            ->toString() ?: 'SOUS_TYPE';

        if (in_array($base, ['OTHER', 'AUTRE'], true)) {
            $base = 'SOUS_TYPE_'.$base;
        }

        $candidate = $base;
        $sequence = 2;

        while (SignalSubType::query()
            ->where('signal_type_id', $signalType->id)
            ->where('code', $candidate)
            ->when($subType, fn ($query) => $query->whereKeyNot($subType->id))
            ->exists()) {
            $suffix = '_'.$sequence;
            $candidate = Str::limit($base, 60 - strlen($suffix), '').$suffix;
            $sequence++;
        }

        return $candidate;
    }

    private function nextSubTypeSortOrder(SignalType $signalType): int
    {
        return ((int) $signalType->subTypes()->max('sort_order')) + 1;
    }

    private function codePart(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    private function ensureSubTypeBelongsToSignalType(SignalType $signalType, SignalSubType $subType): void
    {
        abort_unless((int) $subType->signal_type_id === (int) $signalType->id, 404);
    }

    private function logSubTypeCreation(ActivityLogger $activityLogger, Request $request, SignalType $signalType, SignalSubType $subType): void
    {
        $activityLogger->log(
            'signal_sub_type.created',
            'Creation d un sous-type de signal.',
            $subType,
            [
                'signal_type_id' => $signalType->id,
                'code' => $subType->code,
                'label' => $subType->label,
            ],
            $request
        );
    }
}
