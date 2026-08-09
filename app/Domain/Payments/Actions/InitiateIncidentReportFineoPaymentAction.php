<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Reports\Actions\CreateIncidentReportAction;
use App\Models\IncidentReport;
use App\Models\IncidentReportPaymentSession;
use App\Models\PricingRule;
use App\Models\PublicUser;
use App\Services\Media\SignalVideoConverter;
use App\Services\Payments\FineoPayClient;
use App\Services\WasabiService;
use App\Support\Audit\ActivityLogger;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Throwable;

class InitiateIncidentReportFineoPaymentAction
{
    public function __construct(
        private readonly CreateIncidentReportAction $createIncidentReportAction,
        private readonly FineoPayClient $fineoPayClient,
        private readonly WasabiService $wasabiService,
        private readonly SignalVideoConverter $signalVideoConverter,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function handle(PublicUser $user, array $payload, ?UploadedFile $signalAttachmentFile = null): IncidentReportPaymentSession
    {
        $this->createIncidentReportAction->validateForPayment($user, $payload);
        $pricingRule = $this->resolvePricingRule($user);
        $syncRef = $this->generateSyncRef();
        $signalAttachment = $signalAttachmentFile
            ? $this->storePendingSignalAttachment($signalAttachmentFile, $syncRef, $user)
            : null;

        $this->logReportStep($user, 'public.report.payment_session_database_started', 'Création de la session de paiement en base.', [
            'sync_ref' => $syncRef,
            'pricing_rule_id' => $pricingRule->id,
            'amount' => (int) $pricingRule->amount,
            'currency' => $pricingRule->currency,
            'has_signal_attachment' => $signalAttachment !== null,
        ]);

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

        $this->logReportStep($user, 'public.report.payment_session_database_completed', 'Session de paiement créée en base.', [
            'sync_ref' => $syncRef,
            'payment_session_id' => $session->id,
            'has_signal_attachment' => $signalAttachment !== null,
        ]);

        $this->logReportStep($user, 'public.report.fineopay_checkout_started', 'Demande du lien de paiement FineoPay.', [
            'sync_ref' => $syncRef,
            'payment_session_id' => $session->id,
            'amount' => (int) $pricingRule->amount,
            'currency' => $pricingRule->currency,
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

        $this->logReportStep($user, 'public.report.fineopay_checkout_completed', 'Lien de paiement FineoPay reçu.', [
            'sync_ref' => $syncRef,
            'payment_session_id' => $session->id,
            'has_checkout_link' => filled($checkoutLink),
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

    private function storePendingSignalAttachment(UploadedFile $file, string $syncRef, PublicUser $user): array
    {
        $this->logReportStep($user, 'public.report.signal_attachment_conversion_started', 'Début de préparation de la pièce jointe du signalement.', [
            'sync_ref' => $syncRef,
            'signal_attachment' => $this->uploadedFileForLog($file),
        ]);

        $normalized = $this->signalVideoConverter->normalizeForSignalAttachment($file);
        $fileToUpload = $normalized['file'];

        $this->logReportStep($user, 'public.report.signal_attachment_conversion_completed', 'Pièce jointe prête pour le stockage distant.', [
            'sync_ref' => $syncRef,
            'converted_to_mp4' => (bool) ($normalized['converted'] ?? false),
            'original' => $normalized['original'] ?? null,
            'prepared_file' => $this->uploadedFileForLog($fileToUpload),
        ]);

        try {
            $this->logReportStep($user, 'public.report.signal_attachment_wasabi_started', 'Début du téléversement de la pièce jointe sur Wasabi.', [
                'sync_ref' => $syncRef,
                'prepared_file' => $this->uploadedFileForLog($fileToUpload),
            ]);

            $path = $this->wasabiService->uploadFile(
                $fileToUpload,
                config('wasabi.report_signal_directory', 'reports/signals').'/pending/'.$syncRef,
                'attachment'
            );
        } finally {
            $this->signalVideoConverter->cleanup($normalized['temporary_path'] ?? null);
        }

        if (! $path) {
            throw ValidationException::withMessages([
                'signal_attachment' => ['Impossible de televerser le fichier sur le stockage distant.'],
            ]);
        }

        $mimeType = $fileToUpload->getMimeType() ?: 'application/octet-stream';
        $payload = [
            'type' => str_starts_with($mimeType, 'video/') ? 'video' : 'image',
            'name' => $fileToUpload->getClientOriginalName() ?: 'piece-jointe-signalement',
            'mime_type' => $mimeType,
            'size' => $fileToUpload->getSize(),
            'path' => $path,
            'conversion' => [
                'converted_to_mp4' => (bool) ($normalized['converted'] ?? false),
                'original' => $normalized['original'] ?? null,
            ],
        ];

        $this->logReportStep($user, 'public.report.signal_attachment_wasabi_completed', 'Pièce jointe téléversée sur Wasabi.', [
            'sync_ref' => $syncRef,
            'stored_signal_attachment' => $payload,
        ]);

        return $payload;
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

    private function logReportStep(PublicUser $user, string $action, string $description, array $properties): void
    {
        try {
            $this->activityLogger->log(
                $action,
                $description,
                IncidentReport::class,
                $properties,
                request(),
                $user,
                'public'
            );
        } catch (Throwable) {
            //
        }
    }

    private function uploadedFileForLog(UploadedFile $file): array
    {
        return [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'client_mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'error' => $file->getError(),
        ];
    }
}
