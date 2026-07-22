<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Reports\Actions\CreateIncidentReportAction;
use App\Models\IncidentReportPaymentSession;
use App\Models\PricingRule;
use App\Models\PublicUser;
use App\Services\Payments\FineoPayClient;
use App\Services\WasabiService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class InitiateIncidentReportFineoPaymentAction
{
    public function __construct(
        private readonly CreateIncidentReportAction $createIncidentReportAction,
        private readonly FineoPayClient $fineoPayClient,
        private readonly WasabiService $wasabiService,
    ) {}

    public function handle(PublicUser $user, array $payload, ?UploadedFile $signalAttachmentFile = null): IncidentReportPaymentSession
    {
        $this->createIncidentReportAction->validateForPayment($user, $payload);
        $pricingRule = $this->resolvePricingRule($user);
        $syncRef = $this->generateSyncRef();
        $signalAttachment = $signalAttachmentFile
            ? $this->storePendingSignalAttachment($signalAttachmentFile, $syncRef)
            : null;

        $session = IncidentReportPaymentSession::query()->create([
            'public_user_id' => $user->id,
            'pricing_rule_id' => $pricingRule->id,
            'sync_ref' => $syncRef,
            'amount' => (int) $pricingRule->amount,
            'currency' => $pricingRule->currency,
            'status' => PaymentStatus::Pending->value,
            'provider' => 'fineopay',
            'payment_context' => 'report',
            'report_payload' => $payload,
            'signal_attachment' => $signalAttachment,
            'initiated_at' => CarbonImmutable::now(),
        ]);

        $checkoutLink = $this->fineoPayClient->createCheckoutLink([
            'title' => $pricingRule->label ?: 'Paiement signalement My-Signal',
            'amount' => (int) $pricingRule->amount,
            'callbackUrl' => $this->callbackUrl(),
            'syncRef' => $syncRef,
        ]);

        $session->update([
            'checkout_link' => $checkoutLink,
            'metadata' => [
                'fineopay_checkout_created_at' => now()->toIso8601String(),
                'public_user_type' => $user->publicUserType?->code,
            ],
        ]);

        return $session->fresh('pricingRule');
    }

    private function resolvePricingRule(PublicUser $user): PricingRule
    {
        $user->loadMissing('publicUserType.pricingRule');
        $pricingRule = $user->publicUserType?->pricingRule;

        if ($pricingRule === null || $pricingRule->status !== 'active') {
            $pricingRule = PricingRule::query()
                ->where('code', 'public_signal_report')
                ->where('status', 'active')
                ->first();
        }

        if ($pricingRule === null) {
            throw ValidationException::withMessages([
                'pricing_rule' => ['Aucune tarification active n’est disponible pour ce signalement.'],
            ]);
        }

        return $pricingRule;
    }

    private function storePendingSignalAttachment(UploadedFile $file, string $syncRef): array
    {
        $path = $this->wasabiService->uploadFile(
            $file,
            config('wasabi.report_signal_directory', 'reports/signals').'/pending/'.$syncRef,
            'attachment'
        );

        if (! $path) {
            throw ValidationException::withMessages([
                'signal_attachment' => ['Impossible de televerser le fichier sur le stockage distant.'],
            ]);
        }

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        return [
            'type' => str_starts_with($mimeType, 'video/') ? 'video' : 'image',
            'name' => $file->getClientOriginalName() ?: 'piece-jointe-signalement',
            'mime_type' => $mimeType,
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
        return 'RPT-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
