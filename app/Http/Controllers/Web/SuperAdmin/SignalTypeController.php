<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Organization;
use App\Models\SignalSubType;
use App\Models\SignalType;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignalTypeController extends Controller
{
    public function index(): View
    {
        $perPage = min(max((int) request()->integer('per_page', 12), 1), 100);
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
            'signalTypes' => $query->orderBy('application_id')->orderBy('organization_id')->orderBy('code')->paginate($perPage)->withQueryString(),
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

    public function import(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', 'exists:organizations,id'],
            'csv_file' => ['required', 'file', 'max:5120'],
        ]);

        $application = Application::query()
            ->whereKey($attributes['application_id'])
            ->where('status', 'active')
            ->firstOrFail();
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

        $rows = $this->readSignalTypeImportRows($request->file('csv_file')->getRealPath());

        if ($rows === []) {
            throw ValidationException::withMessages([
                'csv_file' => ['Le fichier ne contient aucune ligne exploitable.'],
            ]);
        }

        $createdCount = 0;

        DB::transaction(function () use ($rows, $application, $organizationIds, $organizations, &$createdCount): void {
            foreach ($rows as $index => $row) {
                $label = $this->normalizeImportedText($row['libelle'] ?? $row['label'] ?? $row['nom'] ?? null, 180);

                if ($label === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => ['La ligne '.($index + 2).' ne contient pas de libelle.'],
                    ]);
                }

                $defaultSlaHours = $this->normalizeImportedSla($row['sla_defaut_heures'] ?? $row['default_sla_hours'] ?? $row['sla'] ?? null, $index);
                $legacyOrganization = $organizations->count() === 1 ? $organizations->first() : null;
                $signalType = SignalType::query()->create([
                    'application_id' => $application->id,
                    'organization_id' => $legacyOrganization?->id,
                    'network_type' => strtoupper((string) ($legacyOrganization?->code ?: $application->code)),
                    'code' => $this->uniqueSignalTypeCode($application),
                    'label' => $label,
                    'default_sla_hours' => $defaultSlaHours,
                    'description' => $this->normalizeImportedText($row['description'] ?? null) ?: null,
                    'status' => 'active',
                ]);

                $signalType->organizations()->sync($organizationIds->all());
                $createdCount++;
            }
        });

        $activityLogger->log(
            'signal_type.imported',
            'Import CSV de types de signal.',
            SignalType::class,
            [
                'application_id' => $application->id,
                'organization_ids' => $organizationIds->all(),
                'rows_count' => count($rows),
                'created_count' => $createdCount,
            ],
            $request
        );

        return redirect()->route('super-admin.signal-types.index', ['application_id' => $application->id])
            ->with('success', "{$createdCount} type(s) de signal importe(s).");
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        $response = new StreamedResponse(function (): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Libelle', 'Description', 'SLA_defaut_heures'], ';');
            fputcsv($output, ['Frais bancaires abusifs', 'Commissions injustifiees ou frais non annonces.', '24'], ';');
            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition('attachment', 'modele_import_types_de_signaux.csv'));

        return $response;
    }

    public function importSubTypes(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'signal_type_ids' => ['required', 'array', 'min:1'],
            'signal_type_ids.*' => ['integer', 'exists:signal_types,id'],
            'csv_file' => ['required', 'file', 'max:5120'],
        ]);

        $signalTypes = SignalType::query()
            ->whereIn('id', $attributes['signal_type_ids'])
            ->orderBy('label')
            ->get();

        if ($signalTypes->isEmpty()) {
            throw ValidationException::withMessages([
                'signal_type_ids' => ['Selectionnez au moins un type de signal.'],
            ]);
        }

        $rows = $this->readSignalTypeImportRows($request->file('csv_file')->getRealPath());

        if ($rows === []) {
            throw ValidationException::withMessages([
                'csv_file' => ['Le fichier ne contient aucune ligne exploitable.'],
            ]);
        }

        $createdCount = 0;

        DB::transaction(function () use ($rows, $signalTypes, &$createdCount): void {
            foreach ($signalTypes as $signalType) {
                foreach ($rows as $index => $row) {
                    $label = $this->normalizeImportedText($row['libelle'] ?? $row['label'] ?? $row['nom'] ?? null, 180);

                    if ($label === '') {
                        throw ValidationException::withMessages([
                            'csv_file' => ['La ligne '.($index + 2).' ne contient pas de libelle.'],
                        ]);
                    }

                    $sortOrder = $this->normalizeImportedSortOrder($row['ordre'] ?? $row['sort_order'] ?? null, $index);

                    $signalType->subTypes()->create([
                        'code' => $this->uniqueSignalSubTypeCode($signalType, $label),
                        'label' => $label,
                        'description' => $this->normalizeImportedText($row['description'] ?? null) ?: null,
                        'sort_order' => $sortOrder ?? $this->nextSubTypeSortOrder($signalType),
                        'status' => 'active',
                    ]);

                    $createdCount++;
                }
            }
        });

        $firstSignalType = $signalTypes->first();

        $activityLogger->log(
            'signal_sub_type.imported',
            'Import CSV de sous-types de signal.',
            $firstSignalType,
            [
                'signal_type_ids' => $signalTypes->pluck('id')->values()->all(),
                'rows_count' => count($rows),
                'created_count' => $createdCount,
            ],
            $request
        );

        $redirectParameters = $signalTypes->count() === 1 ? ['signal_type_id' => $firstSignalType?->id] : [];

        return redirect()->route('super-admin.signal-sub-types.index', $redirectParameters)
            ->with('success', "{$createdCount} sous-type(s) de signal importe(s) pour ".$signalTypes->count().' type(s) de signal.');
    }

    public function downloadSubTypeImportTemplate(): StreamedResponse
    {
        $response = new StreamedResponse(function (): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Libelle', 'Description', 'Ordre'], ';');
            fputcsv($output, ['Carte bancaire bloquee', 'Carte desactivee ou indisponible sans raison.', '1'], ';');
            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition('attachment', 'modele_import_sous_types_de_signal.csv'));

        return $response;
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
        $perPage = min(max((int) request()->integer('per_page', 15), 1), 100);
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

        if (filled(request('application_id'))) {
            $query->whereHas('signalType', function ($signalTypeQuery): void {
                $signalTypeQuery->where('application_id', request('application_id'));
            });
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
                ->paginate($perPage)
                ->withQueryString(),
            'signalTypes' => SignalType::query()
                ->with(['application', 'organization', 'organizations'])
                ->orderBy('application_id')
                ->orderBy('organization_id')
                ->orderBy('code')
                ->get(),
            'applications' => Application::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
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

    public function destroy(Request $request, SignalType $signalType, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Le type de signal a ete supprime.',
                'deleted_id' => $signalType->id,
            ]);
        }

        return redirect()->route('super-admin.signal-types.index')
            ->with('success', 'Le type de signal a ete supprime.');
    }

    public function destroyAll(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $count = SignalType::query()->count();

        SignalType::query()->delete();

        $activityLogger->log(
            'signal_type.cleared',
            'Vidage du catalogue des types de signal.',
            SignalType::class,
            ['deleted_count' => $count],
            $request
        );

        return redirect()->route('super-admin.signal-types.index')
            ->with('success', "{$count} type(s) de signal supprime(s).");
    }

    public function destroySelected(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'signal_type_ids' => ['required', 'array', 'min:1'],
            'signal_type_ids.*' => ['integer'],
        ]);

        $ids = collect($attributes['signal_type_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $existingIds = SignalType::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($existingIds->isEmpty()) {
            throw ValidationException::withMessages([
                'signal_type_ids' => ['Selectionnez au moins un type de signal valide.'],
            ]);
        }

        $snapshots = SignalType::query()
            ->whereIn('id', $existingIds)
            ->get(['id', 'code', 'label', 'application_id', 'organization_id', 'status'])
            ->map(fn (SignalType $signalType) => $signalType->toArray())
            ->all();

        $count = SignalType::query()
            ->whereIn('id', $existingIds)
            ->delete();

        $activityLogger->log(
            'signal_type.bulk_deleted',
            'Suppression groupée de types de signal.',
            SignalType::class,
            [
                'deleted_count' => $count,
                'items' => $snapshots,
            ],
            $request
        );

        return redirect()->route('super-admin.signal-types.index')
            ->with('success', "{$count} type(s) de signal selectionne(s) supprime(s).");
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

    public function destroySubType(Request $request, SignalType $signalType, SignalSubType $subType, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Le sous-type de signal a ete supprime.',
                'deleted_id' => $subType->id,
            ]);
        }

        return back()
            ->with('success', 'Le sous-type de signal a ete supprime.');
    }

    public function destroySelectedSubTypes(Request $request, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
    {
        $attributes = $request->validate([
            'signal_sub_type_ids' => ['required', 'array', 'min:1'],
            'signal_sub_type_ids.*' => ['integer'],
        ]);

        $ids = collect($attributes['signal_sub_type_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $subTypes = SignalSubType::query()
            ->whereIn('id', $ids)
            ->get(['id', 'signal_type_id', 'code', 'label', 'status']);

        if ($subTypes->isEmpty()) {
            throw ValidationException::withMessages([
                'signal_sub_type_ids' => ['Selectionnez au moins un sous-type de signal valide.'],
            ]);
        }

        $snapshots = $subTypes
            ->map(fn (SignalSubType $subType) => $subType->toArray())
            ->all();
        $deletedIds = $subTypes->pluck('id')->map(fn ($id) => (int) $id)->values();

        $count = SignalSubType::query()
            ->whereIn('id', $deletedIds)
            ->delete();

        $activityLogger->log(
            'signal_sub_type.bulk_deleted',
            'Suppression groupée de sous-types de signal.',
            SignalSubType::class,
            [
                'deleted_count' => $count,
                'items' => $snapshots,
            ],
            $request
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} sous-type(s) de signal supprime(s).",
                'deleted_ids' => $deletedIds,
                'deleted_count' => $count,
            ]);
        }

        return back()
            ->with('success', "{$count} sous-type(s) de signal selectionne(s) supprime(s).");
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

    private function readSignalTypeImportRows(string $path): array
    {
        $delimiter = $this->detectCsvDelimiter($path);
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'csv_file' => ['Impossible de lire le fichier CSV.'],
            ]);
        }

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $data = array_map(fn ($value) => $this->normalizeCsvValue($value), $data);

            if ($data === [null] || collect($data)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($value) => $this->normalizeCsvHeader((string) $value), $data);
                $this->validateImportHeaders($headers);
                continue;
            }

            $row = [];

            foreach ($headers as $key => $header) {
                if ($header === '') {
                    continue;
                }

                $row[$header] = $data[$key] ?? null;
            }

            if (collect($row)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function validateImportHeaders(array $headers): void
    {
        if (collect(['libelle', 'label', 'nom'])->intersect($headers)->isEmpty()) {
            throw ValidationException::withMessages([
                'csv_file' => ['Le fichier doit contenir une colonne Libelle. Colonnes optionnelles : Description, SLA_defaut_heures.'],
            ]);
        }
    }

    private function detectCsvDelimiter(string $path): string
    {
        $line = '';
        $handle = fopen($path, 'rb');

        if ($handle !== false) {
            while (($currentLine = fgets($handle)) !== false) {
                if (trim($currentLine) !== '') {
                    $line = $currentLine;
                    break;
                }
            }

            fclose($handle);
        }

        $delimiters = [';' => substr_count($line, ';'), ',' => substr_count($line, ','), "\t" => substr_count($line, "\t")];
        arsort($delimiters);

        return (string) array_key_first($delimiters);
    }

    private function normalizeCsvHeader(string $header): string
    {
        return Str::of($this->normalizeCsvValue($header))
            ->replace("\xEF\xBB\xBF", '')
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    private function normalizeCsvValue(mixed $value): string
    {
        $text = str_replace("\xEF\xBB\xBF", '', (string) ($value ?? ''));

        if (! mb_check_encoding($text, 'UTF-8')) {
            $converted = mb_convert_encoding($text, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
            $text = $converted !== false ? $converted : iconv('Windows-1252', 'UTF-8//IGNORE', $text);
        }

        return trim($text);
    }

    private function normalizeImportedText(mixed $value, ?int $maxLength = null): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) ($value ?? '')) ?: '');

        if ($text === '' || $maxLength === null || mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength);
    }

    private function normalizeImportedSla(mixed $value, int $rowIndex): ?int
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return null;
        }

        if (! ctype_digit($text)) {
            throw ValidationException::withMessages([
                'csv_file' => ['Le SLA de la ligne '.($rowIndex + 2).' doit etre un nombre entier en heures.'],
            ]);
        }

        $sla = (int) $text;

        if ($sla < 1 || $sla > 999) {
            throw ValidationException::withMessages([
                'csv_file' => ['Le SLA de la ligne '.($rowIndex + 2).' doit etre compris entre 1 et 999 heures.'],
            ]);
        }

        return $sla;
    }

    private function normalizeImportedSortOrder(mixed $value, int $rowIndex): ?int
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return null;
        }

        if (! ctype_digit($text)) {
            throw ValidationException::withMessages([
                'csv_file' => ['L ordre de la ligne '.($rowIndex + 2).' doit etre un nombre entier.'],
            ]);
        }

        $sortOrder = (int) $text;

        if ($sortOrder < 0 || $sortOrder > 9999) {
            throw ValidationException::withMessages([
                'csv_file' => ['L ordre de la ligne '.($rowIndex + 2).' doit etre compris entre 0 et 9999.'],
            ]);
        }

        return $sortOrder;
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
