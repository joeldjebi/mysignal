<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Reports\Actions\PersistIncidentReportDamageAction;
use App\Models\IncidentReport;
use App\Models\IncidentReportPaymentSession;
use App\Models\PricingRule;
use App\Models\PublicUser;
use App\Services\Payments\FineoPayClient;
use App\Services\WasabiService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class InitiateDamageDeclarationFineoPaymentAction
{
    public function __construct(
        private readonly PersistIncidentReportDamageAction $persistDamageAction,
        private readonly FineoPayClient $fineoPayClient,
        private readonly WasabiService $wasabiService,
    ) {}

    public function handle(PublicUser $user, IncidentReport $report, array $payload, UploadedFile $damageAttachmentFile): IncidentReportPaymentSession
    {
        $this->persistDamageAction->validateForPayment($user, $report);

        $existingPending = IncidentReportPaymentSession::query()
            ->where('public_user_id', $user->id)
            ->where('incident_report_id', $report->id)
            ->where('payment_context', 'damage')
            ->where('status', PaymentStatus::Pending->value)
            ->latest('id')
            ->first();

        if ($existingPending !== null) {
            if (array_key_exists('purchase_receipt_id', $payload)) {
                $existingPending->update([
                    'damage_payload' => [
                        ...($existingPending->damage_payload ?? []),
                        'purchase_receipt_id' => $payload['purchase_receipt_id'] ?? null,
                    ],
                ]);
            }

            return $existingPending->fresh(['pricingRule', 'incidentReport']);
        }

        $pricingRule = $this->resolvePricingRule();
        $syncRef = $this->generateSyncRef();
        $damageAttachment = $this->storePendingDamageAttachment($damageAttachmentFile, $syncRef);

        $session = IncidentReportPaymentSession::query()->create([
            'public_user_id' => $user->id,
            'pricing_rule_id' => $pricingRule->id,
            'incident_report_id' => $report->id,
            'sync_ref' => $syncRef,
            'amount' => (int) $pricingRule->amount,
            'currency' => $pricingRule->currency,
            'status' => PaymentStatus::Pending->value,
            'provider' => 'fineopay',
            'payment_context' => 'damage',
            'report_payload' => [],
            'damage_payload' => [
                'damage_summary' => $payload['damage_summary'] ?? null,
                'damage_amount_estimated' => $payload['damage_amount_estimated'] ?? null,
                'damage_notes' => $payload['damage_notes'] ?? null,
                'purchase_receipt_id' => $payload['purchase_receipt_id'] ?? null,
            ],
            'damage_attachment' => $damageAttachment,
            'initiated_at' => CarbonImmutable::now(),
        ]);

        $checkoutLink = $this->fineoPayClient->createCheckoutLink([
            'title' => $pricingRule->label ?: 'Paiement declaration de dommage My-Signal',
            'amount' => (int) $pricingRule->amount,
            'callbackUrl' => $this->callbackUrl(),
            'syncRef' => $syncRef,
        ]);

        $session->update([
            'checkout_link' => $checkoutLink,
            'metadata' => [
                'fineopay_checkout_created_at' => now()->toIso8601String(),
                'public_user_type' => $user->publicUserType?->code,
                'incident_report_reference' => $report->reference,
            ],
        ]);

        return $session->fresh(['pricingRule', 'incidentReport']);
    }

    private function resolvePricingRule(): PricingRule
    {
        $pricingRule = PricingRule::query()
            ->where('code', 'public_damage_declaration')
            ->where('status', 'active')
            ->first();

        if ($pricingRule === null) {
            throw ValidationException::withMessages([
                'pricing_rule' => ['Aucune tarification active n’est disponible pour la déclaration de dommage.'],
            ]);
        }

        return $pricingRule;
    }

    private function storePendingDamageAttachment(UploadedFile $file, string $syncRef): array
    {
        $path = $this->wasabiService->uploadFile(
            $file,
            config('wasabi.report_damage_directory', 'reports/damages').'/pending/'.$syncRef,
            'damage'
        );

        if (! $path) {
            throw ValidationException::withMessages([
                'damage_attachment' => ['Impossible de televerser le justificatif du dommage sur le stockage distant.'],
            ]);
        }

        return [
            'name' => $file->getClientOriginalName() ?: 'justificatif-dommage',
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'path' => $path,
        ];
    }

    private function callbackUrl(): string
    {
        $url = route('api.public.payments.fineopay.callback');
        $token = (string) config('services.fineopay.callback_token');

        return $token !== '' ? $url.'?token='.urlencode($token) : $url;
    }

    private function generateSyncRef(): string
    {
        return 'DMG-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
