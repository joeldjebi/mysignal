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
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrivilegeCardWalletPassController extends Controller
{
    public function issueUrl(Request $request, PrivilegeCard $card, GoogleWalletService $google)
    {
        $this->authorizeCardOwner($request, $card);

        $platform = strtolower((string) $request->query('platform'));

        try {
            $url = match ($platform) {
                'ios' => URL::temporarySignedRoute(
                    'api.public.privilege-cards.pass.apple',
                    now()->addMinutes(5),
                    ['card' => $card->id]
                ),
                'android' => $google->buildSaveUrl($card),
                default => throw ValidationException::withMessages([
                    'platform' => ['La plateforme doit etre ios ou android.'],
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
        ]);
    }

    public function downloadApplePass(PrivilegeCard $card, ApplePassService $apple): BinaryFileResponse
    {
        abort_unless($this->isWalletEligible($card), 404);

        try {
            $path = $apple->build($card);
        } catch (WalletConfigurationException $exception) {
            abort(503, $exception->getMessage());
        }

        return response()
            ->download($path, 'mysignal-privilege-card.pkpass', [
                'Content-Type' => 'application/vnd.apple.pkpass',
            ])
            ->deleteFileAfterSend();
    }

    private function authorizeCardOwner(Request $request, PrivilegeCard $card): void
    {
        abort_unless((int) $card->public_user_id === (int) $request->user('public_api')?->id, 404);
        abort_unless($card->status === 'active', 422, 'Cette carte privilège n’est pas active.');
        abort_unless($card->expires_at === null || $card->expires_at->isFuture(), 422, 'Cette carte privilege est expiree.');
        abort_unless($this->hasPaidSession($card), 422, 'Cette carte privilege doit etre payee avant l ajout au Wallet.');
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
