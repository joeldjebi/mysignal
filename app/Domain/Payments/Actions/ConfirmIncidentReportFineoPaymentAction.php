<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Reports\Actions\CreateIncidentReportAction;
use App\Models\IncidentReport;
use App\Models\IncidentReportPaymentSession;
use App\Models\Payment;
use App\Services\Notifications\IncidentReportNotificationService;
use App\Support\Audit\ActivityLogger;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmIncidentReportFineoPaymentAction
{
    public function __construct(
        private readonly CreateIncidentReportAction $createIncidentReportAction,
        private readonly ActivityLogger $activityLogger,
        private readonly IncidentReportNotificationService $notificationService,
    ) {}

    public function handle(array $payload, ?Request $request = null): IncidentReportPaymentSession
    {
        $syncRef = (string) ($payload['syncRef'] ?? '');
        $status = (string) ($payload['status'] ?? '');
        $amount = (int) ($payload['amount'] ?? 0);

        if ($syncRef === '') {
            throw ValidationException::withMessages([
                'syncRef' => ['La reference de synchronisation est requise.'],
            ]);
        }

        return DB::transaction(function () use ($syncRef, $status, $amount, $payload, $request): IncidentReportPaymentSession {
            $session = IncidentReportPaymentSession::query()
                ->where('sync_ref', $syncRef)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status === PaymentStatus::Paid->value && $session->incident_report_id !== null) {
                return $session->fresh(['incidentReport', 'pricingRule']);
            }

            if ($amount !== (int) $session->amount) {
                $session->update([
                    'status' => PaymentStatus::Failed->value,
                    'metadata' => [
                        ...($session->metadata ?? []),
                        'failure_reason' => 'amount_mismatch',
                        'callback_payload' => $payload,
                    ],
                ]);

                throw ValidationException::withMessages([
                    'amount' => ['Le montant du paiement ne correspond pas au montant attendu.'],
                ]);
            }

            if ($status !== 'success') {
                $session->update([
                    'status' => PaymentStatus::Failed->value,
                    'provider_reference' => $payload['reference'] ?? null,
                    'metadata' => [
                        ...($session->metadata ?? []),
                        'callback_payload' => $payload,
                    ],
                ]);

                return $session->fresh(['incidentReport', 'pricingRule']);
            }

            $user = $session->publicUser()->firstOrFail();
            $report = $this->createIncidentReportAction->handle(
                $user,
                $session->report_payload,
                null,
                $session->signal_attachment,
                true,
            );

            $paidAt = $this->paidAt($payload);

            Payment::query()->create([
                'public_user_id' => $session->public_user_id,
                'incident_report_id' => $report->id,
                'pricing_rule_id' => $session->pricing_rule_id,
                'reference' => $this->generatePaymentReference(),
                'amount' => $session->amount,
                'currency' => $session->currency,
                'status' => PaymentStatus::Paid->value,
                'provider' => 'fineopay',
                'provider_reference' => $payload['reference'] ?? null,
                'initiated_at' => $session->initiated_at,
                'paid_at' => $paidAt,
                'metadata' => [
                    'sync_ref' => $syncRef,
                    'client_account_number' => $payload['clientAccountNumber'] ?? null,
                    'callback_payload' => $payload,
                ],
            ]);

            $report->update([
                'payment_status' => PaymentStatus::Paid->value,
                'paid_at' => $paidAt,
            ]);

            $session->update([
                'incident_report_id' => $report->id,
                'status' => PaymentStatus::Paid->value,
                'provider_reference' => $payload['reference'] ?? null,
                'paid_at' => $paidAt,
                'metadata' => [
                    ...($session->metadata ?? []),
                    'callback_payload' => $payload,
                ],
            ]);

            $this->logAndNotify($report, $request);

            return $session->fresh(['incidentReport', 'pricingRule']);
        });
    }

    private function logAndNotify(IncidentReport $report, ?Request $request): void
    {
        $this->activityLogger->log(
            'public.report.created_after_payment',
            'Creation d un signalement public apres paiement FineoPay.',
            $report,
            [
                'reference' => $report->reference,
                'status' => $report->status,
                'application_id' => $report->application_id,
                'organization_id' => $report->organization_id,
                'signal_code' => $report->signal_code,
                'signal_label' => $report->signal_label,
            ],
            $request,
            $report->publicUser,
            'public',
        );

        $this->notificationService->notifyInstitutionReportCreated($report);
        $this->notificationService->notifyCommunityReportCreated($report);
    }

    private function paidAt(array $payload): CarbonImmutable
    {
        return filled($payload['timestamp'] ?? null)
            ? CarbonImmutable::parse($payload['timestamp'])
            : CarbonImmutable::now();
    }

    private function generatePaymentReference(): string
    {
        return 'PAY-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
