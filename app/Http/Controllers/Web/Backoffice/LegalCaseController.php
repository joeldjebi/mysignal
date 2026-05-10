<?php

namespace App\Http\Controllers\Web\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\ReparationCase;
use App\Models\ReparationCaseHistory;
use App\Models\ReparationCaseStep;
use App\Models\Role;
use App\Models\User;
use App\Services\Notifications\IncidentReportNotificationService;
use App\Services\WasabiService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LegalCaseController extends Controller
{
    private const BAILIFF_STEP_TYPES = [
        'constat_planifie' => 'Constat planifie',
        'constat_realise' => 'Constat realise',
        'rapport_huissier_recu' => 'Rapport huissier recu',
        'diligence_huissier' => 'Diligence huissier',
    ];

    private const LAWYER_STEP_TYPES = [
        'analyse_juridique' => 'Analyse juridique',
        'demande_pieces' => 'Demande de pieces',
        'mise_en_demeure_envoyee' => 'Mise en demeure envoyee',
        'requete_deposee' => 'Requete deposee',
        'audience_programmee' => 'Audience programmee',
        'audience_tenue' => 'Audience tenue',
        'conclusions_deposees' => 'Conclusions deposees',
        'decision_rendue' => 'Decision rendue',
        'transaction_negociee' => 'Transaction negociee',
        'dedommagement_obtenu' => 'Dedommagement obtenu',
        'dossier_clos' => 'Dossier clos',
    ];

    public function index(Request $request): View
    {
        $portal = $this->activePortal($request);
        $query = $this->caseQueryForPortal($request);

        if (filled($request->input('search'))) {
            $search = trim((string) $request->input('search'));
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

        return view('backoffice.legal-cases.index', [
            'cases' => $query->latest('id')->paginate(15)->withQueryString(),
            'portal' => $portal,
        ]);
    }

    public function show(Request $request, ReparationCase $reparationCase): View
    {
        $portal = $this->activePortal($request);
        $this->authorizeCase($request, $reparationCase);

        $reparationCase->load([
            'incidentReport.application',
            'incidentReport.organization',
            'incidentReport.commune',
            'publicUser',
            'organization',
            'application',
            'openedBy',
            'bailiff',
            'lawyer',
            'lawyerAssignedBy',
            'histories.createdBy',
            'steps.assignedTo',
            'steps.createdBy',
        ]);

        return view('backoffice.legal-cases.show', [
            'case' => $reparationCase,
            'portal' => $portal,
            'bailiffStepTypes' => self::BAILIFF_STEP_TYPES,
            'lawyerStepTypes' => self::LAWYER_STEP_TYPES,
            'lawyers' => $portal === 'aoda' ? $this->assignableLawyers() : collect(),
        ]);
    }

    public function storeBailiffStep(
        Request $request,
        ReparationCase $reparationCase,
        WasabiService $wasabiService,
        IncidentReportNotificationService $notificationService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $this->authorizePortal($request, 'huissier', 'BO_REPARATION_CASES_HUISSIER');
        $this->authorizeCase($request, $reparationCase);

        $attributes = $request->validate([
            'step_type' => ['required', 'in:'.implode(',', array_keys(self::BAILIFF_STEP_TYPES))],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:8'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,mp4,mov,avi'],
        ]);

        $attachments = $this->uploadAttachments($request, $wasabiService, 'constats/'.$reparationCase->id, 'constat');
        $step = $this->recordStep($reparationCase, $attributes['step_type'], $attributes['title'], $attributes['summary'], $request->user()?->id, $attachments);

        if ($reparationCase->bailiff_started_at === null) {
            $reparationCase->update([
                'bailiff_started_at' => now(),
                'status' => 'under_review',
            ]);
        }

        $this->recordHistory($reparationCase, 'bailiff_step_added', $step->title, $step->summary, $request->user()?->id, ['step_id' => $step->id, 'actor' => 'huissier']);
        $notificationService->notifyPublicReparationCaseStepAdded($reparationCase, $step);
        $notificationService->notifyInstitutionReparationCaseStepAdded($reparationCase, $step);

        $activityLogger->log('legal_case.bailiff_step_added', 'Ajout d une etape huissier.', $reparationCase, ['step_id' => $step->id], $request);

        return back()->with('success', 'L etape de constat a ete enregistree.');
    }

    public function completeBailiff(
        Request $request,
        ReparationCase $reparationCase,
        IncidentReportNotificationService $notificationService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $this->authorizePortal($request, 'huissier', 'BO_REPARATION_CASES_HUISSIER');
        $this->authorizeCase($request, $reparationCase);

        $attributes = $request->validate([
            'summary' => ['required', 'string', 'max:5000'],
        ]);

        $step = $this->recordStep($reparationCase, 'rapport_huissier_recu', 'Procedure huissier terminee', $attributes['summary'], $request->user()?->id);

        $reparationCase->update([
            'bailiff_completed_at' => now(),
            'status' => 'awaiting_lawyer_assignment',
        ]);

        $this->recordHistory($reparationCase, 'bailiff_completed', 'Procedure huissier terminee', $attributes['summary'], $request->user()?->id, ['step_id' => $step->id]);
        $notificationService->notifyPublicReparationCaseUpdated($reparationCase, 'Constat termine', 'La procedure de constat huissier est terminee. Le dossier passe a l ordre des avocats.', ['event' => 'bailiff_completed']);
        $notificationService->notifyInstitutionReparationCaseUpdated($reparationCase, 'Constat termine', 'La procedure de constat huissier est terminee.', ['event' => 'bailiff_completed']);

        $activityLogger->log('legal_case.bailiff_completed', 'Procedure huissier terminee.', $reparationCase, [], $request);

        return back()->with('success', 'Le dossier est marque termine pour la phase huissier.');
    }

    public function assignLawyer(
        Request $request,
        ReparationCase $reparationCase,
        IncidentReportNotificationService $notificationService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $this->authorizePortal($request, 'aoda', 'BO_REPARATION_CASES_AODA');
        $this->authorizeCase($request, $reparationCase);

        if ($reparationCase->closed_at !== null) {
            throw ValidationException::withMessages([
                'lawyer_user_id' => ['Ce dossier est deja conclu.'],
            ]);
        }

        if ($reparationCase->bailiff_completed_at === null) {
            throw ValidationException::withMessages([
                'lawyer_user_id' => ['Le dossier doit etre termine par l huissier avant attribution a un avocat.'],
            ]);
        }

        $attributes = $request->validate([
            'lawyer_user_id' => ['required', 'integer', 'exists:users,id'],
            'summary' => ['nullable', 'string', 'max:3000'],
        ]);

        $lawyers = $this->assignableLawyers();

        if (! $lawyers->contains('id', (int) $attributes['lawyer_user_id'])) {
            throw ValidationException::withMessages([
                'lawyer_user_id' => ['L avocat selectionne n est pas eligible pour recevoir ce dossier.'],
            ]);
        }

        $reparationCase->update([
            'lawyer_user_id' => $attributes['lawyer_user_id'],
            'lawyer_assigned_by_user_id' => $request->user()?->id,
            'lawyer_assigned_at' => now(),
            'status' => 'lawyer_assigned',
        ]);

        $reparationCase->loadMissing('lawyer');
        $summary = $attributes['summary'] ?? 'Le dossier est attribue a l avocat '.$reparationCase->lawyer?->name.'.';
        $step = $this->recordStep($reparationCase, 'attribue_avocat', 'Avocat attribue', $summary, $request->user()?->id, [], $reparationCase->lawyer_user_id);
        $this->recordHistory($reparationCase, 'lawyer_assigned_by_aoda', 'Avocat attribue par l ordre des avocats', $summary, $request->user()?->id, ['lawyer_user_id' => $reparationCase->lawyer_user_id]);

        $notificationService->notifyBackofficeReparationCaseAssigned($reparationCase, $reparationCase->lawyer_user_id, 'avocat');
        $notificationService->notifyPublicReparationCaseUpdated($reparationCase, 'Avocat attribue', $summary, ['event' => 'lawyer_assigned']);
        $notificationService->notifyInstitutionReparationCaseUpdated($reparationCase, 'Avocat attribue', $summary, ['event' => 'lawyer_assigned']);
        $notificationService->notifyPublicReparationCaseStepAdded($reparationCase, $step);

        $activityLogger->log('legal_case.lawyer_assigned', 'Attribution d un dossier a un avocat.', $reparationCase, ['lawyer_user_id' => $reparationCase->lawyer_user_id], $request);

        return back()->with('success', 'Le dossier a ete attribue a l avocat.');
    }

    public function storeLawyerStep(
        Request $request,
        ReparationCase $reparationCase,
        WasabiService $wasabiService,
        IncidentReportNotificationService $notificationService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $this->authorizePortal($request, 'avocat', 'BO_REPARATION_CASES_AVOCAT');
        $this->authorizeCase($request, $reparationCase);

        if ($reparationCase->lawyer_completed_at !== null) {
            throw ValidationException::withMessages([
                'step_type' => ['La procedure avocat est deja terminee pour ce dossier.'],
            ]);
        }

        $attributes = $request->validate([
            'step_type' => ['required', 'in:'.implode(',', array_keys(self::LAWYER_STEP_TYPES))],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:5000'],
            'mark_completed' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:8'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,mp4,mov,avi'],
        ]);

        $attachments = $this->uploadAttachments($request, $wasabiService, 'procedures-avocat/'.$reparationCase->id, 'procedure');
        $step = $this->recordStep($reparationCase, $attributes['step_type'], $attributes['title'], $attributes['summary'], $request->user()?->id, $attachments);

        $payload = ['status' => 'judicial_in_progress'];

        if ($request->boolean('mark_completed')) {
            $payload['status'] = 'judicial_in_progress';
            $payload['lawyer_completed_at'] = now();
        }

        $reparationCase->update($payload);
        $this->recordHistory($reparationCase, 'lawyer_step_added', $step->title, $step->summary, $request->user()?->id, ['step_id' => $step->id, 'actor' => 'avocat']);
        $notificationService->notifyPublicReparationCaseStepAdded($reparationCase, $step);
        $notificationService->notifyInstitutionReparationCaseStepAdded($reparationCase, $step);

        if ($request->boolean('mark_completed')) {
            $this->recordHistory($reparationCase, 'lawyer_completed', 'Procedure avocat terminee', $attributes['summary'], $request->user()?->id, ['step_id' => $step->id]);
            $notificationService->notifyPublicReparationCaseUpdated($reparationCase, 'Procedure judiciaire terminee', 'La procedure judiciaire du dossier est terminee.', ['event' => 'lawyer_completed']);
        }

        $activityLogger->log('legal_case.lawyer_step_added', 'Ajout d une etape avocat.', $reparationCase, ['step_id' => $step->id], $request);

        return back()->with('success', 'L etape judiciaire a ete enregistree.');
    }

    public function concludeByAoda(
        Request $request,
        ReparationCase $reparationCase,
        IncidentReportNotificationService $notificationService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $this->authorizePortal($request, 'aoda', 'BO_REPARATION_CASES_AODA');
        $this->authorizeCase($request, $reparationCase);

        if ($reparationCase->lawyer_completed_at === null) {
            throw ValidationException::withMessages([
                'status' => ['La procedure avocat doit etre terminee avant conclusion du dossier.'],
            ]);
        }

        if ($reparationCase->closed_at !== null) {
            throw ValidationException::withMessages([
                'status' => ['Ce dossier est deja conclu.'],
            ]);
        }

        $attributes = $request->validate([
            'status' => ['required', 'in:approved,rejected,compensated,closed'],
            'closure_reason' => ['required', 'string', 'max:3000'],
        ]);

        $reparationCase->update([
            'status' => $attributes['status'],
            'closure_reason' => $attributes['closure_reason'],
            'closed_at' => now(),
        ]);

        $statusLabel = $this->conclusionStatusLabel($attributes['status']);
        $summary = 'Conclusion AODA : '.$statusLabel.'. '.$attributes['closure_reason'];

        $step = $this->recordStep($reparationCase, 'dossier_clos', 'Conclusion AODA', $summary, $request->user()?->id);
        $this->recordHistory($reparationCase, 'aoda_concluded', 'Dossier conclu par AODA', $summary, $request->user()?->id, [
            'status' => $attributes['status'],
            'step_id' => $step->id,
        ]);

        $notificationService->notifyPublicReparationCaseUpdated($reparationCase, 'Dossier conclu', $summary, ['event' => 'aoda_concluded']);
        $notificationService->notifyInstitutionReparationCaseUpdated($reparationCase, 'Dossier conclu', $summary, ['event' => 'aoda_concluded']);

        $activityLogger->log('legal_case.aoda_concluded', 'Conclusion d un dossier par AODA.', $reparationCase, ['status' => $attributes['status']], $request);

        return back()->with('success', 'Le dossier a ete conclu et la victime a ete notifiee.');
    }

    private function caseQueryForPortal(Request $request)
    {
        $portal = $this->activePortal($request);
        $userId = (int) $request->user()->id;

        $query = ReparationCase::query()
            ->with(['incidentReport', 'publicUser', 'organization', 'application', 'bailiff', 'lawyer']);

        return match ($portal) {
            'huissier' => $query->where('bailiff_user_id', $userId),
            'aoda' => $query->whereNotNull('bailiff_completed_at'),
            'avocat' => $query->where('lawyer_user_id', $userId),
            default => abort(403),
        };
    }

    private function authorizeCase(Request $request, ReparationCase $case): void
    {
        $portal = $this->activePortal($request);
        $userId = (int) $request->user()->id;

        match ($portal) {
            'huissier' => abort_if((int) $case->bailiff_user_id !== $userId, 404),
            'aoda' => abort_if($case->bailiff_completed_at === null, 404),
            'avocat' => abort_if((int) $case->lawyer_user_id !== $userId, 404),
            default => abort(403),
        };
    }

    private function authorizePortal(Request $request, string $portal, string $permissionCode): void
    {
        abort_if($this->activePortal($request) !== $portal, 403);
        abort_if(! $request->user()?->hasEffectivePermissionCode($permissionCode), 403);
    }

    private function activePortal(Request $request): string
    {
        $portal = $request->attributes->get('super_admin_access')?->portal
            ?: $request->user()?->getRelationValue('activeAccess')?->portal;

        abort_if(! in_array($portal, ['huissier', 'aoda', 'avocat'], true), 403);

        return $portal;
    }

    private function assignableLawyers(): Collection
    {
        $roleIds = Role::query()
            ->whereIn('code', ['AVOCAT', 'LAWYER'])
            ->orWhere('name', 'like', '%AVOCAT%')
            ->orWhere('name', 'like', '%LAWYER%')
            ->pluck('id');

        return User::query()
            ->where('status', 'active')
            ->where(function ($query) use ($roleIds): void {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('roles.id', $roleIds))
                    ->orWhereHas('accesses', fn ($accessQuery) => $accessQuery->where('portal', 'avocat')->where('status', 'active'));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function uploadAttachments(Request $request, WasabiService $wasabiService, string $directory, string $prefix): array
    {
        if (! $request->hasFile('attachments')) {
            return [];
        }

        return collect($request->file('attachments', []))
            ->filter()
            ->map(function ($file) use ($wasabiService, $directory, $prefix): array {
                return [
                    'path' => $wasabiService->uploadFile($file, $directory, $prefix),
                    'name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            })
            ->values()
            ->all();
    }

    private function recordStep(ReparationCase $case, string $stepType, string $title, string $summary, ?int $createdByUserId, array $attachments = [], ?int $assignedToUserId = null): ReparationCaseStep
    {
        return ReparationCaseStep::query()->create([
            'reparation_case_id' => $case->id,
            'assigned_to_user_id' => $assignedToUserId,
            'created_by_user_id' => $createdByUserId,
            'step_type' => $stepType,
            'status' => 'completed',
            'title' => $title,
            'summary' => $summary,
            'completed_at' => now(),
            'is_visible_to_public' => true,
            'meta' => $attachments === [] ? null : ['attachments' => $attachments],
        ]);
    }

    private function recordHistory(ReparationCase $case, string $eventType, string $title, ?string $description, ?int $userId, array $meta = []): void
    {
        ReparationCaseHistory::query()->create([
            'reparation_case_id' => $case->id,
            'created_by_user_id' => $userId,
            'event_type' => $eventType,
            'title' => $title,
            'description' => filled($description) ? $description : null,
            'is_visible_to_public' => true,
            'meta' => $meta ?: null,
        ]);
    }

    private function conclusionStatusLabel(string $status): string
    {
        return match ($status) {
            'approved' => 'valide',
            'rejected' => 'rejete',
            'compensated' => 'compense',
            'closed' => 'clos',
            default => $status,
        };
    }
}
