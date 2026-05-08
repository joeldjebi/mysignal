<?php

namespace App\Services\Notifications;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Models\IncidentReport;
use App\Models\IncidentReportNotificationContext;
use App\Models\Meter;
use App\Models\PublicUser;
use App\Models\ReparationCase;
use App\Models\ReparationCaseStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class IncidentReportNotificationService
{
    private const NEARBY_RADIUS_METERS = 1000;

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
            'report',
        );
    }

    public function notifyInstitutionDamageDeclared(IncidentReport $report): void
    {
        $this->notifyInstitutionUsers(
            $report,
            'institution_damage_declared',
            'Dommage declare',
            'Un dommage a ete declare sur le signalement '.$report->reference.'.',
            'damages',
            'damage',
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

    public function notifyCommunityReportCreated(IncidentReport $report): void
    {
        $report->loadMissing(['meter', 'publicUser']);

        $this->notifyHouseholdReportCreated($report);
        $this->notifyNearbyReportCreated($report);
    }

    public function notifyCommunityReportResolved(IncidentReport $report): void
    {
        $report->loadMissing(['meter', 'publicUser']);

        $this->resolutionContextsForReport($report)
            ->each(function (IncidentReportNotificationContext $context) use ($report): void {
                $recipientIds = collect($context->recipient_public_user_ids ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn (int $id) => $id > 0 && $id !== (int) $report->public_user_id)
                    ->unique()
                    ->values();

                if ($recipientIds->isEmpty()) {
                    $context->update(['resolved_notified_at' => now()]);

                    return;
                }

                PublicUser::query()
                    ->whereIn('id', $recipientIds)
                    ->where('status', 'active')
                    ->get()
                    ->each(function (PublicUser $publicUser) use ($report, $context): void {
                        $this->dispatcher->notifyPublicUser(
                            $publicUser,
                            'community_report_resolved',
                            'Probleme resolu',
                            'Le probleme signale sur '.$this->reportSubjectLabel($report).' a ete marque comme resolu.',
                            $this->communityReportPayload($report, [
                                'event' => 'resolved',
                                'context_type' => $context->context_type,
                                'context_id' => $context->id,
                            ]),
                        );
                    });

                $context->update(['resolved_notified_at' => now()]);
            });
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
                'url' => route('backoffice.legal-cases.show', $case),
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
                'url' => route('backoffice.legal-cases.show', $case),
            ]),
        );
    }

    private function notifyInstitutionUsers(IncidentReport $report, string $type, string $title, string $body, string $screen, string $category = 'report'): void
    {
        $organizationId = $this->reportOrganizationId($report);

        if ($organizationId === null) {
            return;
        }

        User::query()
            ->where(function (Builder $query) use ($organizationId): void {
                $query
                    ->where('organization_id', $organizationId)
                    ->orWhereHas('accesses', function (Builder $accessQuery) use ($organizationId): void {
                        $accessQuery
                            ->where('portal', 'institution')
                            ->where('status', 'active')
                            ->where('organization_id', $organizationId);
                    });
            })
            ->where('status', 'active')
            ->get()
            ->unique('id')
            ->each(function (User $user) use ($report, $type, $title, $body, $screen, $category, $organizationId): void {
                $this->dispatcher->notifyInstitutionUser(
                    $user,
                    $type,
                    $title,
                    $body,
                    [
                        'category' => $category,
                        'screen' => $screen,
                        'source' => 'public_user',
                        'report_id' => $report->id,
                        'report_reference' => $report->reference,
                        'public_user_id' => $report->public_user_id,
                        'application_id' => $report->application_id,
                        'organization_id' => $organizationId,
                        'signal_code' => $report->signal_code,
                        'status' => $report->status,
                        'damage_resolution_status' => $report->damage_resolution_status,
                    ],
                );
            });
    }

    private function reportOrganizationId(IncidentReport $report): ?int
    {
        if ($report->organization_id !== null) {
            return (int) $report->organization_id;
        }

        $report->loadMissing('meter');

        return $report->meter?->organization_id !== null
            ? (int) $report->meter->organization_id
            : null;
    }

    private function notifyHouseholdReportCreated(IncidentReport $report): void
    {
        if ($report->meter_id === null) {
            return;
        }

        $households = \App\Models\Household::query()
            ->where('status', 'active')
            ->whereHas('members', function (Builder $memberQuery) use ($report): void {
                $memberQuery
                    ->where('status', 'active')
                    ->whereHas('publicUser.meters', fn (Builder $meterQuery) => $meterQuery->whereKey($report->meter_id));
            })
            ->with(['members' => fn ($query) => $query->where('status', 'active')])
            ->get();

        foreach ($households as $household) {
            $recipientIds = $household->members
                ->pluck('public_user_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0 && $id !== (int) $report->public_user_id)
                ->unique()
                ->values()
                ->all();

            if ($recipientIds === []) {
                continue;
            }

            $existingContext = $this->openHouseholdContext($report, (int) $household->id);

            if ($existingContext) {
                $this->mergeContextRecipients($existingContext, [$report->public_user_id, ...$recipientIds]);

                continue;
            }

            $context = IncidentReportNotificationContext::query()->create([
                'incident_report_id' => $report->id,
                'context_type' => 'household',
                'household_id' => $household->id,
                'organization_id' => $report->organization_id,
                'meter_id' => $report->meter_id,
                'signal_code' => $report->signal_code,
                'recipient_public_user_ids' => $recipientIds,
                'notified_at' => now(),
            ]);

            $this->notifyCommunityRecipients(
                $report,
                $context,
                $recipientIds,
                'Signalement dans votre Gbonhi',
                'Un probleme a ete signale sur '.$this->reportSubjectLabel($report).'.',
                'household_report_created',
            );
        }
    }

    private function notifyNearbyReportCreated(IncidentReport $report): void
    {
        $coordinates = $this->reportCoordinates($report);

        if ($coordinates === null || $report->organization_id === null) {
            return;
        }

        $existingContext = $this->openNearbyContext($report, $coordinates['latitude'], $coordinates['longitude']);
        $recipientIds = $this->nearbyPublicUserIds($report, $coordinates['latitude'], $coordinates['longitude']);

        if ($recipientIds === []) {
            return;
        }

        if ($existingContext) {
            $this->mergeContextRecipients($existingContext, [$report->public_user_id, ...$recipientIds]);

            return;
        }

        $context = IncidentReportNotificationContext::query()->create([
            'incident_report_id' => $report->id,
            'context_type' => 'nearby',
            'organization_id' => $report->organization_id,
            'meter_id' => $report->meter_id,
            'signal_code' => $report->signal_code,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'radius_meters' => self::NEARBY_RADIUS_METERS,
            'recipient_public_user_ids' => $recipientIds,
            'notified_at' => now(),
        ]);

        $this->notifyCommunityRecipients(
            $report,
            $context,
            $recipientIds,
            'Probleme signale pres de vous',
            'Un probleme '.$this->organizationLabel($report).' a ete signale dans un rayon de 1 km.',
            'nearby_report_created',
        );
    }

    private function resolutionContextsForReport(IncidentReport $report): \Illuminate\Support\Collection
    {
        $contexts = $report->notificationContexts()
            ->whereNull('resolved_notified_at')
            ->get();

        if ($report->meter_id !== null) {
            $householdIds = \App\Models\Household::query()
                ->where('status', 'active')
                ->whereHas('members', function (Builder $memberQuery) use ($report): void {
                    $memberQuery
                        ->where('status', 'active')
                        ->whereHas('publicUser.meters', fn (Builder $meterQuery) => $meterQuery->whereKey($report->meter_id));
                })
                ->pluck('id')
                ->all();

            if ($householdIds !== []) {
                $contexts = $contexts->merge(
                    IncidentReportNotificationContext::query()
                        ->where('context_type', 'household')
                        ->whereIn('household_id', $householdIds)
                        ->where('meter_id', $report->meter_id)
                        ->where('signal_code', $report->signal_code)
                        ->whereNull('resolved_notified_at')
                        ->get()
                );
            }
        }

        $coordinates = $this->reportCoordinates($report);

        if ($coordinates !== null && $report->organization_id !== null) {
            $contexts = $contexts->merge(
                IncidentReportNotificationContext::query()
                    ->where('context_type', 'nearby')
                    ->where('organization_id', $report->organization_id)
                    ->where('signal_code', $report->signal_code)
                    ->whereNull('resolved_notified_at')
                    ->get()
                    ->filter(function (IncidentReportNotificationContext $context) use ($coordinates): bool {
                        if ($context->latitude === null || $context->longitude === null) {
                            return false;
                        }

                        return $this->distanceInMeters(
                            $coordinates['latitude'],
                            $coordinates['longitude'],
                            (float) $context->latitude,
                            (float) $context->longitude
                        ) <= self::NEARBY_RADIUS_METERS;
                    })
            );
        }

        return $contexts
            ->unique('id')
            ->values();
    }

    private function notifyCommunityRecipients(
        IncidentReport $report,
        IncidentReportNotificationContext $context,
        array $recipientIds,
        string $title,
        string $body,
        string $type
    ): void {
        PublicUser::query()
            ->whereIn('id', $recipientIds)
            ->where('status', 'active')
            ->get()
            ->each(function (PublicUser $publicUser) use ($report, $context, $title, $body, $type): void {
                $this->dispatcher->notifyPublicUser(
                    $publicUser,
                    $type,
                    $title,
                    $body,
                    $this->communityReportPayload($report, [
                        'event' => 'created',
                        'context_type' => $context->context_type,
                        'context_id' => $context->id,
                    ]),
                );
            });
    }

    private function openHouseholdContext(IncidentReport $report, int $householdId): ?IncidentReportNotificationContext
    {
        return IncidentReportNotificationContext::query()
            ->where('context_type', 'household')
            ->where('household_id', $householdId)
            ->where('meter_id', $report->meter_id)
            ->where('signal_code', $report->signal_code)
            ->whereNull('resolved_notified_at')
            ->whereHas('incidentReport', fn (Builder $query) => $this->openReportQuery($query))
            ->latest('id')
            ->first();
    }

    private function openNearbyContext(IncidentReport $report, float $latitude, float $longitude): ?IncidentReportNotificationContext
    {
        return IncidentReportNotificationContext::query()
            ->where('context_type', 'nearby')
            ->where('organization_id', $report->organization_id)
            ->where('signal_code', $report->signal_code)
            ->whereNull('resolved_notified_at')
            ->whereHas('incidentReport', fn (Builder $query) => $this->openReportQuery($query))
            ->get()
            ->first(function (IncidentReportNotificationContext $context) use ($latitude, $longitude): bool {
                if ($context->latitude === null || $context->longitude === null) {
                    return false;
                }

                return $this->distanceInMeters($latitude, $longitude, (float) $context->latitude, (float) $context->longitude) <= self::NEARBY_RADIUS_METERS;
            });
    }

    private function openReportQuery(Builder $query): void
    {
        $query->whereNotIn('status', [
            IncidentReportStatus::Resolved->value,
            IncidentReportStatus::Rejected->value,
        ]);
    }

    private function mergeContextRecipients(IncidentReportNotificationContext $context, array $recipientIds): void
    {
        $mergedRecipientIds = collect($context->recipient_public_user_ids ?? [])
            ->merge($recipientIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $context->update([
            'recipient_public_user_ids' => $mergedRecipientIds,
        ]);
    }

    private function nearbyPublicUserIds(IncidentReport $report, float $latitude, float $longitude): array
    {
        $deltaLatitude = self::NEARBY_RADIUS_METERS / 111_320;
        $deltaLongitude = self::NEARBY_RADIUS_METERS / max(1, (111_320 * cos(deg2rad($latitude))));

        return Meter::query()
            ->where('organization_id', $report->organization_id)
            ->whereKeyNot($report->meter_id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $deltaLatitude, $latitude + $deltaLatitude])
            ->whereBetween('longitude', [$longitude - $deltaLongitude, $longitude + $deltaLongitude])
            ->with(['publicUsers' => fn ($query) => $query->where('public_users.status', 'active')])
            ->get()
            ->filter(fn (Meter $meter): bool => $this->distanceInMeters($latitude, $longitude, (float) $meter->latitude, (float) $meter->longitude) <= self::NEARBY_RADIUS_METERS)
            ->flatMap(fn (Meter $meter) => $meter->publicUsers->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0 && $id !== (int) $report->public_user_id)
            ->unique()
            ->values()
            ->all();
    }

    private function reportCoordinates(IncidentReport $report): ?array
    {
        $report->loadMissing('meter');

        $latitude = $report->latitude ?? $report->meter?->latitude;
        $longitude = $report->longitude ?? $report->meter?->longitude;

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
        ];
    }

    private function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6_371_000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function communityReportPayload(IncidentReport $report, array $extraData = []): array
    {
        return [
            'category' => 'report',
            'screen' => 'reports',
            'source' => 'community',
            'report_id' => $report->id,
            'report_reference' => $report->reference,
            'meter_id' => $report->meter_id,
            'organization_id' => $report->organization_id,
            'signal_code' => $report->signal_code,
            'status' => $report->status,
            ...$extraData,
        ];
    }

    private function reportSubjectLabel(IncidentReport $report): string
    {
        $report->loadMissing('meter');

        return $report->meter?->meter_number
            ? 'l identifiant '.$report->meter->meter_number
            : 'un identifiant de votre zone';
    }

    private function organizationLabel(IncidentReport $report): string
    {
        $report->loadMissing('organization');

        return $report->organization?->name
            ? 'chez '.$report->organization->name
            : 'dans votre zone';
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
            ->where(function (Builder $query) use ($case): void {
                $query
                    ->where('organization_id', $case->organization_id)
                    ->orWhereHas('accesses', function (Builder $accessQuery) use ($case): void {
                        $accessQuery
                            ->where('portal', 'institution')
                            ->where('status', 'active')
                            ->where('organization_id', $case->organization_id);
                    });
            })
            ->where('status', 'active')
            ->get()
            ->unique('id')
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
