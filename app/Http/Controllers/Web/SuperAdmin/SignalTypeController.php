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
            'signalType' => $signalType->load(['subTypes' => fn ($query) => $query->orderBy('sort_order')->orderBy('label')]),
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
            'code', 'label', 'application_id', 'organization_id', 'default_sla_hours', 'description', 'status',
        ]);
        $signalType->update($this->validatedAttributes($request, $signalType));

        $activityLogger->log(
            'signal_type.updated',
            'Mise a jour d un type de signal.',
            $signalType,
            [
                'before' => $before,
                'after' => $signalType->only([
                    'code', 'label', 'application_id', 'organization_id', 'default_sla_hours', 'description', 'status',
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

    public function storeSubType(Request $request, SignalType $signalType, ActivityLogger $activityLogger): RedirectResponse
    {
        $subType = $signalType->subTypes()->create($this->validatedSubTypeAttributes($request, $signalType));

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

        return redirect()->route('super-admin.signal-types.edit', $signalType)
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
        ];
    }

    private function validatedSubTypeAttributes(Request $request, SignalType $signalType, ?SignalSubType $subType = null): array
    {
        $attributes = $request->validate([
            'code' => [
                'required',
                'string',
                'max:60',
                Rule::unique('signal_sub_types', 'code')
                    ->where('signal_type_id', $signalType->id)
                    ->ignore($subType?->id),
                Rule::notIn(['OTHER', 'other', 'Other']),
            ],
            'label' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        return [
            'code' => strtoupper($attributes['code']),
            'label' => $attributes['label'],
            'description' => $attributes['description'] ?? null,
            'sort_order' => $attributes['sort_order'] ?? 0,
            'status' => $subType?->status ?? 'active',
        ];
    }

    private function ensureSubTypeBelongsToSignalType(SignalType $signalType, SignalSubType $subType): void
    {
        abort_unless((int) $subType->signal_type_id === (int) $signalType->id, 404);
    }
}
