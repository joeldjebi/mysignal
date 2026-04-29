<?php

namespace App\Services\Notifications;

use App\Models\IncidentReport;
use App\Models\ReparationCase;
use App\Models\ReparationCaseStep;
use App\Models\User;

class IncidentReportNotificationService
{
    public function __construct(private readonly PushNotificationDispatcher $dispatcher)
    {
    }

    public function notifyInstitutionReportCreated(IncidentReport $report): void
    {
        $this->notifyInstitutionUsers(
            $report,
            'institution_report_created',
            'Nouveau signalement',
            'Un nouveau signalement '.$report->reference.' a ete soumis.',
            'reports.show',
        );
    }

    public function notifyInstitutionDamageDeclared(IncidentReport $report): void
    {
        $this->notifyInstitutionUsers(
            $report,
            'institution_damage_declared',
            'Dommage declare',
            'Un dommage a ete declare sur le signalement '.$report->reference.'.',
            'reports.show',
        );
    }

    public function notifyPublicReportAction(IncidentReport $report, string $action, string $title, string $body): void
    {
        $report->loadMissing('publicUser');

        if (! $report->publicUser) {
            return;
        }

        $this->dispatcher->notifyPublicUser(
            $report->publicUser,
            $action,
            $title,
            $body,
            [
                'category' => 'report',
                'screen' => 'reports',
                'source' => 'institution',
                'report_id' => $report->id,
                'report_reference' => $report->reference,
                'status' => $report->status,
                'damage_resolution_status' => $report->damage_resolution_status,
            ],
        );
    }

    public function notifyPublicReparationCaseOpened(ReparationCase $case): void
    {
        $case->loadMissing(['publicUser', 'incidentReport']);

        if (! $case->publicUser) {
            return;
        }

        $this->dispatcher->notifyPublicUser(
            $case->publicUser,
            'reparation_case_opened',
            'Dossier ouvert',
            'Un dossier a ete ouvert pour le signalement '.$case->incidentReport?->reference.'.',
            $this->reparationCasePayload($case, [
                'event' => 'opened',
            ]),
        );
    }

    public function notifyInstitutionReparationCaseOpened(ReparationCase $case): void
    {
        $case->loadMissing('incidentReport');

        $this->notifyInstitutionUsersForCase(
            $case,
            'institution_reparation_case_opened',
            'Dossier ouvert',
            'Un dossier a ete ouvert pour le signalement '.$case->incidentReport?->reference.'.',
            [
                'event' => 'opened',
            ],
        );
    }

    public function notifyPublicReparationCaseUpdated(ReparationCase $case, string $title, string $body, array $extraData = []): void
    {
        $case->loadMissing(['publicUser', 'incidentReport']);

        if (! $case->publicUser) {
            return;
        }

        $this->dispatcher->notifyPublicUser(
            $case->publicUser,
            'reparation_case_updated',
            $title,
            $body,
            $this->reparationCasePayload($case, [
                'event' => 'updated',
                ...$extraData,
            ]),
        );
    }

    public function notifyInstitutionReparationCaseUpdated(ReparationCase $case, string $title, string $body, array $extraData = []): void
    {
        $this->notifyInstitutionUsersForCase($case, 'institution_reparation_case_updated', $title, $body, [
            'event' => 'updated',
            ...$extraData,
        ]);
    }

    public function notifyPublicReparationCaseStepAdded(ReparationCase $case, ReparationCaseStep $step): void
    {
        $case->loadMissing(['publicUser', 'incidentReport']);

        if (! $case->publicUser) {
            return;
        }

        $this->dispatcher->notifyPublicUser(
            $case->publicUser,
            'reparation_case_step_added',
            'Nouvelle etape du dossier',
            $step->title.' - '.$this->stepStatusLabel($step->status).'.',
            $this->reparationCasePayload($case, [
                'event' => 'step_added',
                'step_id' => $step->id,
                'step_type' => $step->step_type,
                'step_status' => $step->status,
            ]),
        );
    }

    public function notifyInstitutionReparationCaseStepAdded(ReparationCase $case, ReparationCaseStep $step): void
    {
        $this->notifyInstitutionUsersForCase(
            $case,
            'institution_reparation_case_step_added',
            'Nouvelle etape du dossier',
            $step->title.' - '.$this->stepStatusLabel($step->status).'.',
            [
                'event' => 'step_added',
                'step_id' => $step->id,
                'step_type' => $step->step_type,
                'step_status' => $step->status,
            ],
        );
    }

    public function notifyBackofficeReparationCaseAssigned(ReparationCase $case, ?int $userId, string $assignmentLabel): void
    {
        if ($userId === null) {
            return;
        }

        $case->loadMissing('incidentReport');
        $user = User::query()
            ->whereKey($userId)
            ->where('status', 'active')
            ->first();

        if (! $user) {
            return;
        }

        $this->dispatcher->notifyBackofficeUser(
            $user,
            'backoffice_reparation_case_assigned',
            'Dossier affecte',
            'Le dossier '.$case->reference.' vous a ete affecte comme '.$assignmentLabel.'.',
            $this->reparationCasePayload($case, [
                'event' => 'assigned',
                'assignment_role' => $assignmentLabel,
                'assigned_user_id' => $user->id,
                'url' => route('super-admin.reparation-cases.show', $case),
            ]),
        );
    }

    public function notifyBackofficeReparationCaseStepAssigned(ReparationCase $case, ReparationCaseStep $step): void
    {
        if ($step->assigned_to_user_id === null) {
            return;
        }

        $case->loadMissing('incidentReport');
        $user = User::query()
            ->whereKey($step->assigned_to_user_id)
            ->where('status', 'active')
            ->first();

        if (! $user) {
            return;
        }

        $this->dispatcher->notifyBackofficeUser(
            $user,
            'backoffice_reparation_case_step_assigned',
            'Etape affectee',
            'L etape "'.$step->title.'" du dossier '.$case->reference.' vous a ete affectee.',
            $this->reparationCasePayload($case, [
                'event' => 'step_assigned',
                'step_id' => $step->id,
                'step_type' => $step->step_type,
                'step_status' => $step->status,
                'assigned_user_id' => $user->id,
                'url' => route('super-admin.reparation-cases.show', $case),
            ]),
        );
    }

    private function notifyInstitutionUsers(IncidentReport $report, string $type, string $title, string $body, string $screen): void
    {
        if ($report->organization_id === null) {
            return;
        }

        User::query()
            ->where('organization_id', $report->organization_id)
            ->where('status', 'active')
            ->get()
            ->each(function (User $user) use ($report, $type, $title, $body, $screen): void {
                $this->dispatcher->notifyInstitutionUser(
                    $user,
                    $type,
                    $title,
                    $body,
                    [
                        'category' => 'report',
                        'screen' => $screen,
                        'source' => 'public_user',
                        'report_id' => $report->id,
                        'report_reference' => $report->reference,
                        'public_user_id' => $report->public_user_id,
                        'application_id' => $report->application_id,
                        'organization_id' => $report->organization_id,
                        'signal_code' => $report->signal_code,
                        'status' => $report->status,
                    ],
                );
            });
    }

    private function reparationCasePayload(ReparationCase $case, array $extraData = []): array
    {
        return [
            'category' => 'reparation_case',
            'screen' => 'reparation_cases',
            'source' => 'super_admin',
            'reparation_case_id' => $case->id,
            'reparation_case_reference' => $case->reference,
            'report_id' => $case->incident_report_id,
            'report_reference' => $case->incidentReport?->reference,
            'status' => $case->status,
            'case_type' => $case->case_type,
            ...$extraData,
        ];
    }

    private function notifyInstitutionUsersForCase(ReparationCase $case, string $type, string $title, string $body, array $extraData = []): void
    {
        if ($case->organization_id === null) {
            return;
        }

        User::query()
            ->where('organization_id', $case->organization_id)
            ->where('status', 'active')
            ->get()
            ->each(function (User $user) use ($case, $type, $title, $body, $extraData): void {
                $this->dispatcher->notifyInstitutionUser(
                    $user,
                    $type,
                    $title,
                    $body,
                    [
                        ...$this->reparationCasePayload($case, $extraData),
                        'source' => 'super_admin',
                        'screen' => 'reparation_cases',
                    ],
                );
            });
    }

    private function stepStatusLabel(?string $status): string
    {
        return [
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'completed' => 'Terminee',
            'cancelled' => 'Annulee',
        ][$status] ?? (string) $status;
    }
}
