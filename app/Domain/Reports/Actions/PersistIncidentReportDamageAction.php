<?php

namespace App\Domain\Reports\Actions;

use App\Models\IncidentReport;
use App\Models\PublicUser;
use App\Services\Notifications\IncidentReportNotificationService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PersistIncidentReportDamageAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly IncidentReportNotificationService $notificationService,
    ) {}

    public function validateForPayment(PublicUser $user, IncidentReport $report): void
    {
        $this->validateDamageDeclaration($user, $report, true);
    }

    public function handle(
        PublicUser $user,
        IncidentReport $report,
        array $payload,
        array $damageAttachment,
        ?Request $request = null,
    ): IncidentReport {
        $this->validateDamageDeclaration($user, $report, false);

        $report->update([
            'damage_summary' => $payload['damage_summary'] ?? null,
            'damage_amount_estimated' => $payload['damage_amount_estimated'] ?? null,
            'damage_notes' => $payload['damage_notes'] ?? null,
            'damage_attachment' => $damageAttachment,
            'damage_declared_at' => now(),
            'damage_resolution_status' => 'submitted',
            'damage_resolution_notes' => null,
            'damage_resolved_at' => null,
        ]);

        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'payments.pricingRule']);

        $this->activityLogger->log(
            'public.report.damage_declared',
            'Declaration d un dommage sur un signalement apres paiement.',
            $report,
            [
                'reference' => $report->reference,
                'damage_resolution_status' => $report->damage_resolution_status,
                'damage_amount_estimated' => $report->damage_amount_estimated,
                'has_damage_attachment' => filled($report->damage_attachment),
            ],
            $request
        );

        $this->notificationService->notifyInstitutionDamageDeclared($report);

        return $report;
    }

    private function validateDamageDeclaration(PublicUser $user, IncidentReport $report, bool $enforceWindow): void
    {
        if ((int) $report->public_user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'report_id' => ['Ce signalement est introuvable pour cet usager.'],
            ]);
        }

        if ($report->resolution_confirmation_status !== 'confirmed') {
            throw ValidationException::withMessages([
                'report_id' => ['Confirmez d abord la resolution du signalement avant d enregistrer un dommage.'],
            ]);
        }

        if ($report->damage_declared_at !== null) {
            throw ValidationException::withMessages([
                'report_id' => ['Le dommage pour ce signalement a deja ete enregistre.'],
            ]);
        }

        if ($report->resolution_confirmed_at === null) {
            throw ValidationException::withMessages([
                'report_id' => ['La date de confirmation de resolution est introuvable pour ce signalement.'],
            ]);
        }

        if ($enforceWindow && now()->greaterThan($report->resolution_confirmed_at->copy()->addDay())) {
            throw ValidationException::withMessages([
                'report_id' => ['Le delai de 24h pour declarer un dommage apres confirmation de resolution est depasse.'],
            ]);
        }
    }
}
