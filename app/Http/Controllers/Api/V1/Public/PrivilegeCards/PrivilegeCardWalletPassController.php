<?php

namespace App\Http\Controllers\Api\V1\Public\PrivilegeCards;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PrivilegeCard;
use App\Models\PrivilegeCardPaymentSession;
use App\Services\Wallet\ApplePassService;
use App\Services\Wallet\GoogleWalletService;
use App\Services\Wallet\WalletConfigurationException;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class PrivilegeCardWalletPassController extends Controller
{
    public function issueUrl(Request $request, PrivilegeCard $card, GoogleWalletService $google)
    {
        $this->authorizeCardOwner($request, $card);

        $platform = strtolower((string) $request->query('platform'));
        $appleUrl = $this->applePassUrl($card);

        try {
            $androidUrl = in_array($platform, ['', 'android'], true) ? $google->buildSaveUrl($card) : null;
            $url = match ($platform) {
                '' => null,
                'ios' => $appleUrl,
                'android' => $androidUrl,
                default => throw ValidationException::withMessages([
                    'platform' => ['La plateforme doit être ios ou android.'],
                ]),
            };
        } catch (WalletConfigurationException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 503);
        }

        return ApiResponse::success([
            'url' => $url,
            'platform' => $platform ?: 'all',
            'apple_url' => $appleUrl,
            'android_url' => $androidUrl,
            'links' => [
                'apple' => $appleUrl,
                'android' => $androidUrl,
            ],
            'expires_at' => now()->addMinutes(5)->toIso8601String(),
        ]);
    }

    public function downloadApplePass(Request $request, PrivilegeCard $card, ApplePassService $apple): BinaryFileResponse|Response
    {
        $expires = (int) $request->query('expires');

        if ($expires <= 0 || $expires < now()->timestamp) {
            return $this->walletError($request, 'Ce lien Apple Wallet a expiré. Générez un nouveau lien depuis votre espace.', 403);
        }

        if (! $this->isWalletEligible($card)) {
            return $this->walletError($request, $this->walletEligibilityMessage($card), 422);
        }

        try {
            $path = $apple->build($card);
        } catch (WalletConfigurationException $exception) {
            return $this->walletError($request, $exception->getMessage(), 503);
        }

        return response()
            ->download($path, 'mysignal-privilege-card.pkpass', [
                'Content-Type' => 'application/vnd.apple.pkpass',
            ])
            ->deleteFileAfterSend();
    }

    private function walletError(Request $request, string $message, int $status): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return response($message, $status)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function walletEligibilityMessage(PrivilegeCard $card): string
    {
        if ($card->status !== 'active') {
            return 'Cette carte privilège n’est pas active.';
        }

        if ($card->expires_at !== null && ! $card->expires_at->isFuture()) {
            return 'Cette carte privilège est expirée.';
        }

        if (! $this->hasPaidSession($card)) {
            return 'Cette carte privilège doit être payée avant l’ajout au Wallet.';
        }

        return 'Cette carte privilège ne peut pas être ajoutée au Wallet pour le moment.';
    }

    private function applePassUrl(PrivilegeCard $card): string
    {
        return route('api.public.privilege-cards.pass.apple', [
            'card' => $card->id,
            'expires' => now()->addMinutes(5)->timestamp,
        ]);
    }

    private function authorizeCardOwner(Request $request, PrivilegeCard $card): void
    {
        abort_unless((int) $card->public_user_id === (int) $request->user('public_api')?->id, 404);
        abort_unless($card->status === 'active', 422, 'Cette carte privilège n’est pas active.');
        abort_unless($card->expires_at === null || $card->expires_at->isFuture(), 422, 'Cette carte privilège est expirée.');
        abort_unless($this->hasPaidSession($card), 422, 'Cette carte privilège doit être payée avant l’ajout au Wallet.');
    }

    private function isWalletEligible(PrivilegeCard $card): bool
    {
        return $card->status === 'active'
            && ($card->expires_at === null || $card->expires_at->isFuture())
            && $this->hasPaidSession($card);
    }

    private function hasPaidSession(PrivilegeCard $card): bool
    {
        return PrivilegeCardPaymentSession::query()
            ->where('privilege_card_id', $card->id)
            ->where('public_user_id', $card->public_user_id)
            ->where('status', PaymentStatus::Paid->value)
            ->exists();
    }
}
