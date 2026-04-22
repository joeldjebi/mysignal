<?php

namespace App\Http\Controllers\Api\V1\Partner\Auth;

use App\Domain\Auth\Actions\RequestPartnerOtpAction;
use App\Domain\Auth\Actions\ResetPartnerPasswordAction;
use App\Domain\Auth\Actions\VerifyPartnerOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Auth\RequestPartnerPasswordResetOtpRequest;
use App\Http\Requests\Api\V1\Partner\Auth\ResetPartnerPasswordRequest;
use App\Http\Requests\Api\V1\Partner\Auth\VerifyPartnerPasswordResetOtpRequest;
use App\Http\Resources\Api\V1\Partner\Auth\PartnerUserResource;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;

class PartnerPasswordResetController extends Controller
{
    public function requestOtp(RequestPartnerPasswordResetOtpRequest $request, RequestPartnerOtpAction $action, ActivityLogger $activityLogger)
    {
        $result = $action->handle($request->string('phone')->value());

        $activityLogger->log(
            'partner.password_reset_otp_requested',
            'Demande de code OTP partenaire pour reinitialisation du mot de passe.',
            'partner_auth',
            ['phone' => $result->phone],
            $request,
            null,
            'partner',
        );

        return ApiResponse::success([
            'phone' => $result->phone,
            'expires_at' => $result->expiresAt,
            'otp_code_for_testing' => app()->environment('local', 'testing') ? $result->code : null,
        ], 'OTP envoye avec succes.');
    }

    public function verifyOtp(VerifyPartnerPasswordResetOtpRequest $request, VerifyPartnerOtpAction $action, ActivityLogger $activityLogger)
    {
        $verification = $action->handle(
            $request->string('phone')->value(),
            $request->string('code')->value(),
        );

        $activityLogger->log(
            'partner.password_reset_otp_verified',
            'Verification OTP partenaire reussie.',
            'partner_auth',
            ['phone' => $verification->phone],
            $request,
            null,
            'partner',
        );

        return ApiResponse::success([
            'phone' => $verification->phone,
            'verification_token' => $verification->token,
            'expires_at' => $verification->expires_at->toIso8601String(),
        ], 'Numero verifie avec succes.');
    }

    public function resetPassword(ResetPartnerPasswordRequest $request, ResetPartnerPasswordAction $action, ActivityLogger $activityLogger)
    {
        $user = $action->handle(
            $request->string('phone')->value(),
            $request->string('verification_token')->value(),
            $request->string('password')->value(),
        );

        $activityLogger->log(
            'partner.password_reset_completed',
            'Reinitialisation du mot de passe partenaire.',
            $user,
            [
                'organization_id' => $user->organization_id,
                'phone' => $user->phone,
            ],
            $request,
            $user,
            'partner',
        );

        return ApiResponse::success([
            'user' => new PartnerUserResource($user),
        ], 'Mot de passe reinitialise avec succes.');
    }
}
