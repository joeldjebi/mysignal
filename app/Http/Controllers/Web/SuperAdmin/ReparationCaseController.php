<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\ReparationCase;
use App\Models\ReparationCaseHistory;
use App\Models\ReparationCaseStep;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit\ActivityLogger;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReparationCaseController extends Controller
{
    private const CASE_TYPES = [
        'precontentieux' => 'Precontentieux',
        'judiciaire' => 'Judiciaire',
    ];

    private const PRIORITIES = [
        'low' => 'Faible',
        'normal' => 'Normale',
        'high' => 'Haute',
        'critical' => 'Critique',
    ];

    private const STEP_TYPES = [
        'dossier_ouvert' => 'Dossier ouvert',
        'attribue_huissier' => 'Huissier attribue',
        'constat_planifie' => 'Constat planifie',
        'constat_realise' => 'Constat realise',
        'rapport_huissier_recu' => 'Rapport huissier recu',
        'attribue_avocat' => 'Avocat attribue',
        'mise_en_demeure_envoyee' => 'Mise en demeure envoyee',
        'procedure_lancee' => 'Procedure lancee',
        'audience_programmee' => 'Audience programmee',
        'decision_rendue' => 'Decision rendue',
        'dedommagement_obtenu' => 'Dedommagement obtenu',
        'dossier_clos' => 'Dossier clos',
    ];

    private const STEP_STATUSES = [
        'pending' => 'En attente',
        'in_progress' => 'En cours',
        'completed' => 'Terminee',
        'cancelled' => 'Annulee',
    ];

    public function index(): View
    {
        $query = ReparationCase::query()->with(['incidentReport', 'publicUser', 'organization', 'application', 'assignedTo', 'bailiff', 'lawyer']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhereHas('incidentReport', fn ($reportQuery) => $reportQuery
                        ->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('signal_label', 'like', '%'.$search.'%')
                        ->orWhere('signal_code', 'like', '%'.$search.'%'))
                    ->orWhereHas('publicUser', fn ($userQuery) => $userQuery
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%'));
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', request('organization_id'));
        }

        return view('super-admin.reparation-cases.index', [
            'reparationCases' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'incident_report_id' => ['required', 'integer', 'exists:incident_reports,id'],
            'opening_notes' => ['nullable', 'string', 'max:3000'],
            'case_type' => ['nullable', 'in:precontentieux,judiciaire'],
            'priority' => ['nullable', 'in:low,normal,high,critical'],
        ]);

        $report = IncidentReport::query()
            ->with(['reparationCase', 'publicUser', 'organization', 'application'])
            ->findOrFail($attributes['incident_report_id']);

        if ($report->reparationCase !== null) {
            throw ValidationException::withMessages([
                'incident_report_id' => ['Un dossier de réparation existe déjà pour ce signalement.'],
            ]);
        }

        $eligibility = $this->resolveEligibility($report);

        if ($eligibility === null) {
            throw ValidationException::withMessages([
                'incident_report_id' => ['Ce signalement n est pas encore éligible à l ouverture manuelle d un dossier de réparation.'],
            ]);
        }

        $case = ReparationCase::query()->create([
            'incident_report_id' => $report->id,
            'public_user_id' => $report->public_user_id,
            'application_id' => $report->application_id,
            'organization_id' => $report->organization_id,
            'opened_by_user_id' => $request->user()?->id,
            'reference' => $this->generateReference(),
            'case_type' => $attributes['case_type'] ?? 'precontentieux',
            'priority' => $attributes['priority'] ?? 'normal',
            'status' => 'submitted',
            'eligibility_reason' => $eligibility,
            'opening_notes' => $attributes['opening_notes'] ?? null,
            'damage_summary' => $report->damage_summary,
            'damage_amount_claimed' => $report->damage_amount_estimated,
            'opened_at' => now(),
            'meta' => [
                'sla_state' => $this->resolveSlaState($report),
                'damage_declared' => $report->damage_declared_at !== null,
            ],
        ]);

        $this->recordHistory(
            $case,
            'opened',
            'Dossier ouvert',
            $attributes['opening_notes'] ?? 'Le dossier a ete ouvert a partir du signalement source.',
            $request->user()?->id
        );

        $this->recordStep(
            $case,
            'dossier_ouvert',
            'Dossier ouvert',
            'completed',
            $attributes['opening_notes'] ?? 'Le dossier a ete ouvert depuis le signalement source.',
            null,
            now(),
            true,
            $request->user()?->id
        );

        $activityLogger->log(
            'reparation_case.opened',
            'Ouverture d un dossier contentieux.',
            $case,
            [
                'reference' => $case->reference,
                'case_type' => $case->case_type,
                'priority' => $case->priority,
                'status' => $case->status,
                'incident_report_id' => $case->incident_report_id,
                'eligibility_reason' => $case->eligibility_reason,
            ],
            $request
        );

        return redirect()->route('super-admin.reparation-cases.show', $case)
            ->with('success', 'Le dossier de réparation a été ouvert.');
    }

    public function show(ReparationCase $reparationCase): View
    {
        return view('super-admin.reparation-cases.show', [
            'reparationCase' => $reparationCase->load([
                'incidentReport.commune',
                'incidentReport.city',
                'incidentReport.country',
                'incidentReport.meter',
                'publicUser',
                'organization',
                'application',
                'openedBy',
                'assignedTo',
                'bailiff.roles',
                'lawyer.roles',
                'histories.createdBy',
                'steps.assignedTo',
                'steps.createdBy',
            ]),
            'assignableUsers' => User::query()->where('is_super_admin', true)->orderBy('name')->get(['id', 'name', 'email']),
            'bailiffUsers' => $this->resolveAssignableUsersByRole(['HUISSIER', 'BAILIFF']),
            'lawyerUsers' => $this->resolveAssignableUsersByRole(['AVOCAT', 'LAWYER']),
            'resolvedSignalPayload' => $reparationCase->incidentReport?->resolvedSignalPayload() ?? [],
            'resolvedDamageAttachment' => $reparationCase->incidentReport?->resolvedDamageAttachment(),
            'slaState' => $this->resolveSlaState($reparationCase->incidentReport),
            'caseTypes' => self::CASE_TYPES,
            'priorities' => self::PRIORITIES,
            'stepTypes' => self::STEP_TYPES,
            'stepStatuses' => self::STEP_STATUSES,
        ]);
    }

    public function update(Request $request, ReparationCase $reparationCase, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'case_type' => ['required', 'in:precontentieux,judiciaire'],
            'priority' => ['required', 'in:low,normal,high,critical'],
            'status' => ['required', 'in:submitted,under_review,awaiting_documents,sent_to_organization,organization_responded,approved,rejected,compensated,closed'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'bailiff_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'lawyer_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'damage_amount_validated' => ['nullable', 'numeric', 'min:0'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
            'closure_reason' => ['nullable', 'string', 'max:3000'],
        ]);

        $closingStatuses = ['approved', 'rejected', 'compensated', 'closed'];
        $originalCaseType = $reparationCase->case_type;
        $originalPriority = $reparationCase->priority;
        $originalStatus = $reparationCase->status;
        $originalAssignedToUserId = $reparationCase->assigned_to_user_id;
        $originalBailiffUserId = $reparationCase->bailiff_user_id;
        $originalLawyerUserId = $reparationCase->lawyer_user_id;
        $originalValidatedAmount = $reparationCase->damage_amount_validated;
        $originalResolutionNotes = $reparationCase->resolution_notes;
        $originalClosureReason = $reparationCase->closure_reason;

        $reparationCase->update([
            'case_type' => $attributes['case_type'],
            'priority' => $attributes['priority'],
            'status' => $attributes['status'],
            'assigned_to_user_id' => $attributes['assigned_to_user_id'] ?? null,
            'bailiff_user_id' => $attributes['bailiff_user_id'] ?? null,
            'lawyer_user_id' => $attributes['lawyer_user_id'] ?? null,
            'damage_amount_validated' => $attributes['damage_amount_validated'] ?? null,
            'resolution_notes' => $attributes['resolution_notes'] ?? null,
            'closure_reason' => $attributes['closure_reason'] ?? null,
            'closed_at' => in_array($attributes['status'], $closingStatuses, true) ? now() : null,
        ]);

        $reparationCase->loadMissing(['assignedTo', 'bailiff', 'lawyer']);

        if ($originalCaseType !== $reparationCase->case_type) {
            $this->recordHistory(
                $reparationCase,
                'case_type_updated',
                'Type de dossier mis a jour',
                'Le dossier est maintenant qualifie comme '.$this->caseTypeLabel($reparationCase->case_type).'.',
                $request->user()?->id,
                ['case_type' => $reparationCase->case_type]
            );
        }

        if ($originalPriority !== $reparationCase->priority) {
            $this->recordHistory(
                $reparationCase,
                'priority_updated',
                'Priorite du dossier mise a jour',
                'La priorite du dossier est maintenant '.$this->priorityLabel($reparationCase->priority).'.',
                $request->user()?->id,
                ['priority' => $reparationCase->priority]
            );
        }

        if ($originalStatus !== $reparationCase->status) {
            $this->recordHistory(
                $reparationCase,
                'status_updated',
                'Statut du dossier mis a jour',
                'Le dossier est maintenant au statut : '.$this->statusLabel($reparationCase->status).'.',
                $request->user()?->id,
                ['status' => $reparationCase->status]
            );
        }

        if ((string) $originalAssignedToUserId !== (string) $reparationCase->assigned_to_user_id) {
            $assignedLabel = $reparationCase->assignedTo?->name
                ? 'Le dossier est maintenant suivi par '.$reparationCase->assignedTo->name.'.'
                : 'Le dossier n est plus assigne a un responsable.';

            $this->recordHistory(
                $reparationCase,
                'assignment_updated',
                'Responsable du dossier mis a jour',
                $assignedLabel,
                $request->user()?->id,
                ['assigned_to_user_id' => $reparationCase->assigned_to_user_id]
            );
        }

        if ((string) $originalBailiffUserId !== (string) $reparationCase->bailiff_user_id) {
            $bailiffLabel = $reparationCase->bailiff?->name
                ? 'Le dossier est maintenant attribue a l huissier '.$reparationCase->bailiff->name.'.'
                : 'Aucun huissier n est actuellement attribue au dossier.';

            $this->recordHistory(
                $reparationCase,
                'bailiff_updated',
                'Huissier mis a jour',
                $bailiffLabel,
                $request->user()?->id,
                ['bailiff_user_id' => $reparationCase->bailiff_user_id]
            );

            if ($reparationCase->bailiff_user_id) {
                $this->recordStep(
                    $reparationCase,
                    'attribue_huissier',
                    'Huissier attribue',
                    'completed',
                    $bailiffLabel,
                    $reparationCase->bailiff_user_id,
                    now(),
                    true,
                    $request->user()?->id
                );
            }
        }

        if ((string) $originalLawyerUserId !== (string) $reparationCase->lawyer_user_id) {
            $lawyerLabel = $reparationCase->lawyer?->name
                ? 'Le dossier est maintenant suivi par l avocat '.$reparationCase->lawyer->name.'.'
                : 'Aucun avocat n est actuellement attribue au dossier.';

            $this->recordHistory(
                $reparationCase,
                'lawyer_updated',
                'Avocat mis a jour',
                $lawyerLabel,
                $request->user()?->id,
                ['lawyer_user_id' => $reparationCase->lawyer_user_id]
            );

            if ($reparationCase->lawyer_user_id) {
                $this->recordStep(
                    $reparationCase,
                    'attribue_avocat',
                    'Avocat attribue',
                    'completed',
                    $lawyerLabel,
                    $reparationCase->lawyer_user_id,
                    now(),
                    true,
                    $request->user()?->id
                );
            }
        }

        if ((string) $originalValidatedAmount !== (string) $reparationCase->damage_amount_validated && $reparationCase->damage_amount_validated !== null) {
            $this->recordHistory(
                $reparationCase,
                'validated_amount_updated',
                'Montant valide mis a jour',
                'Le montant valide est desormais de '.number_format((float) $reparationCase->damage_amount_validated, 0, ',', ' ').' FCFA.',
                $request->user()?->id,
                ['damage_amount_validated' => (float) $reparationCase->damage_amount_validated]
            );
        }

        if (($attributes['resolution_notes'] ?? null) && $originalResolutionNotes !== $reparationCase->resolution_notes) {
            $this->recordHistory(
                $reparationCase,
                'processing_note_added',
                'Nouvelle mise a jour du dossier',
                $reparationCase->resolution_notes,
                $request->user()?->id
            );
        }

        if (($attributes['closure_reason'] ?? null) && $originalClosureReason !== $reparationCase->closure_reason) {
            $this->recordHistory(
                $reparationCase,
                'closure_reason_updated',
                'Motif de cloture renseigne',
                $reparationCase->closure_reason,
                $request->user()?->id
            );
        }

        $activityLogger->log(
            'reparation_case.updated',
            'Mise a jour d un dossier contentieux.',
            $reparationCase,
            [
                'reference' => $reparationCase->reference,
                'before' => [
                    'case_type' => $originalCaseType,
                    'priority' => $originalPriority,
                    'status' => $originalStatus,
                    'assigned_to_user_id' => $originalAssignedToUserId,
                    'bailiff_user_id' => $originalBailiffUserId,
                    'lawyer_user_id' => $originalLawyerUserId,
                    'damage_amount_validated' => $originalValidatedAmount,
                    'resolution_notes' => $originalResolutionNotes,
                    'closure_reason' => $originalClosureReason,
                ],
                'after' => [
                    'case_type' => $reparationCase->case_type,
                    'priority' => $reparationCase->priority,
                    'status' => $reparationCase->status,
                    'assigned_to_user_id' => $reparationCase->assigned_to_user_id,
                    'bailiff_user_id' => $reparationCase->bailiff_user_id,
                    'lawyer_user_id' => $reparationCase->lawyer_user_id,
                    'damage_amount_validated' => $reparationCase->damage_amount_validated,
                    'resolution_notes' => $reparationCase->resolution_notes,
                    'closure_reason' => $reparationCase->closure_reason,
                ],
            ],
            $request
        );

        return back()->with('success', 'Le dossier de réparation a été mis à jour.');
    }

    public function storeStep(Request $request, ReparationCase $reparationCase, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'step_type' => ['required', 'in:'.implode(',', array_keys(self::STEP_TYPES))],
            'status' => ['required', 'in:'.implode(',', array_keys(self::STEP_STATUSES))],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'is_visible_to_public' => ['nullable', 'boolean'],
        ]);

        $step = $this->recordStep(
            $reparationCase,
            $attributes['step_type'],
            $attributes['title'],
            $attributes['status'],
            $attributes['summary'] ?? null,
            $attributes['assigned_to_user_id'] ?? null,
            isset($attributes['completed_at']) ? CarbonImmutable::parse($attributes['completed_at']) : null,
            (bool) ($attributes['is_visible_to_public'] ?? false),
            $request->user()?->id,
            isset($attributes['due_at']) ? CarbonImmutable::parse($attributes['due_at']) : null
        );

        $assignedName = $step->assignedTo?->name ? ' Responsable : '.$step->assignedTo->name.'.' : '';

        $this->recordHistory(
            $reparationCase,
            'step_added',
            'Nouvelle etape de procedure',
            $step->title.'. '.$this->stepStatusLabel($step->status).'.'.$assignedName,
            $request->user()?->id,
            ['step_type' => $step->step_type, 'step_id' => $step->id]
        );

        $activityLogger->log(
            'reparation_case.step_added',
            'Ajout d une etape de procedure.',
            $reparationCase,
            [
                'reference' => $reparationCase->reference,
                'step_id' => $step->id,
                'step_type' => $step->step_type,
                'status' => $step->status,
                'assigned_to_user_id' => $step->assigned_to_user_id,
                'is_visible_to_public' => $step->is_visible_to_public,
            ],
            $request
        );

        return back()->with('success', 'L etape de procedure a ete enregistree.');
    }

    private function recordHistory(
        ReparationCase $reparationCase,
        string $eventType,
        string $title,
        ?string $description,
        ?int $userId,
        array $meta = []
    ): void {
        ReparationCaseHistory::query()->create([
            'reparation_case_id' => $reparationCase->id,
            'created_by_user_id' => $userId,
            'event_type' => $eventType,
            'title' => $title,
            'description' => filled($description) ? $description : null,
            'is_visible_to_public' => true,
            'meta' => $meta ?: null,
        ]);
    }

    private function recordStep(
        ReparationCase $reparationCase,
        string $stepType,
        string $title,
        string $status,
        ?string $summary,
        ?int $assignedToUserId,
        $completedAt,
        bool $isVisibleToPublic,
        ?int $createdByUserId,
        $dueAt = null
    ): ReparationCaseStep {
        return ReparationCaseStep::query()->create([
            'reparation_case_id' => $reparationCase->id,
            'assigned_to_user_id' => $assignedToUserId,
            'created_by_user_id' => $createdByUserId,
            'step_type' => $stepType,
            'status' => $status,
            'title' => $title,
            'summary' => filled($summary) ? $summary : null,
            'due_at' => $dueAt,
            'completed_at' => $completedAt,
            'is_visible_to_public' => $isVisibleToPublic,
        ]);
    }

    private function resolveEligibility(IncidentReport $report): ?string
    {
        $slaState = $this->resolveSlaState($report);
        $hasDamage = $report->damage_declared_at !== null || filled($report->damage_summary) || filled($report->damage_amount_estimated);

        if ($slaState['code'] === 'breached' && $hasDamage) {
            return 'sla_breached_and_damage_declared';
        }

        if ($slaState['code'] === 'breached') {
            return 'sla_breached';
        }

        if ($hasDamage) {
            return 'damage_declared';
        }

        return null;
    }

    private function resolveSlaState(?IncidentReport $report): array
    {
        if ($report === null || blank($report->target_sla_hours) || blank($report->created_at)) {
            return [
                'code' => 'unconfigured',
                'label' => 'Sans configuration TCM',
                'elapsed_hours' => null,
            ];
        }

        $endReference = $report->resolved_at ?? now();
        $elapsedHours = round($report->created_at->diffInMinutes($endReference) / 60, 1);
        $ratio = $report->target_sla_hours > 0 ? ($elapsedHours / $report->target_sla_hours) : 0;

        if ($ratio >= 1) {
            return ['code' => 'breached', 'label' => 'SLA depasse', 'elapsed_hours' => $elapsedHours];
        }

        if ($ratio >= 0.8) {
            return ['code' => 'risk', 'label' => 'SLA a risque', 'elapsed_hours' => $elapsedHours];
        }

        return ['code' => 'within', 'label' => 'Dans le TCM', 'elapsed_hours' => $elapsedHours];
    }

    private function generateReference(): string
    {
        return 'REP-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    private function statusLabel(string $status): string
    {
        return [
            'submitted' => 'Soumis',
            'under_review' => 'En analyse',
            'awaiting_documents' => 'Pieces requises',
            'sent_to_organization' => 'Transmis a l organisation',
            'organization_responded' => 'Reponse organisation',
            'approved' => 'Valide',
            'rejected' => 'Rejete',
            'compensated' => 'Compense',
            'closed' => 'Clos',
        ][$status] ?? $status;
    }

    private function caseTypeLabel(string $caseType): string
    {
        return self::CASE_TYPES[$caseType] ?? $caseType;
    }

    private function priorityLabel(string $priority): string
    {
        return self::PRIORITIES[$priority] ?? $priority;
    }

    private function stepStatusLabel(string $status): string
    {
        return self::STEP_STATUSES[$status] ?? $status;
    }

    private function resolveAssignableUsersByRole(array $codes): \Illuminate\Support\Collection
    {
        $roles = Role::query()
            ->whereIn('code', $codes)
            ->orWhere(function ($query) use ($codes): void {
                foreach ($codes as $code) {
                    $query->orWhere('name', 'like', '%'.$code.'%');
                }
            })
            ->pluck('id');

        if ($roles->isNotEmpty()) {
            return User::query()
                ->where('status', 'active')
                ->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $roles))
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return User::query()
            ->where('status', 'active')
            ->where('is_super_admin', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
