<?php

namespace App\Http\Controllers\Api\V1\Public\Auth;

use App\Domain\Auth\Actions\LoginPublicUserAction;
use App\Domain\Auth\Actions\RegisterPublicUserAction;
use App\Domain\Auth\Actions\RequestPublicOtpAction;
use App\Domain\Auth\Actions\ResetPublicPasswordAction;
use App\Domain\Auth\Actions\VerifyPublicOtpAction;
use App\Domain\Auth\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Auth\LoginPublicUserRequest;
use App\Http\Requests\Api\V1\Public\Auth\RegisterPublicUserRequest;
use App\Http\Requests\Api\V1\Public\Auth\RequestPublicPasswordResetOtpRequest;
use App\Http\Requests\Api\V1\Public\Auth\RequestOtpRequest;
use App\Http\Requests\Api\V1\Public\Auth\ResetPublicPasswordRequest;
use App\Http\Requests\Api\V1\Public\Auth\VerifyPublicPasswordResetOtpRequest;
use App\Http\Requests\Api\V1\Public\Auth\VerifyOtpRequest;
use App\Http\Resources\Api\V1\Public\Auth\PublicUserResource;
use App\Support\Audit\ActivityLogger;
use App\Support\Api\ApiResponse;
use App\Support\Auth\PublicApiTokenTtl;
use Illuminate\Http\Request;

class PublicAuthController extends Controller
{
    public function requestOtp(RequestOtpRequest $request, RequestPublicOtpAction $action, ActivityLogger $activityLogger)
    {
        $result = $action->handle($request->string('phone')->value());

        $activityLogger->log(
            'public.otp_requested',
            'Demande de code OTP.',
            'public_auth',
            [
                'phone' => $request->string('phone')->value(),
            ],
            $request,
            null,
            'public',
        );

        return ApiResponse::success([
            'phone' => $result->phone,
            'expires_at' => $result->expiresAt,
            'otp_code_for_testing' => app()->environment('local', 'testing') ? $result->code : null,
        ], 'OTP envoye avec succes.');
    }

    public function verifyOtp(VerifyOtpRequest $request, VerifyPublicOtpAction $action, ActivityLogger $activityLogger)
    {
        $verification = $action->handle(
            $request->string('phone')->value(),
            $request->string('code')->value(),
        );

        $activityLogger->log(
            'public.otp_verified',
            'Verification OTP reussie.',
            'public_auth',
            [
                'phone' => $request->string('phone')->value(),
            ],
            $request,
            null,
            'public',
        );

        return ApiResponse::success([
            'phone' => $verification->phone,
            'verification_token' => $verification->token,
            'expires_at' => $verification->expires_at->toIso8601String(),
        ], 'Numero verifie avec succes.');
    }

    public function register(RegisterPublicUserRequest $request, RegisterPublicUserAction $action, ActivityLogger $activityLogger)
    {
        $result = $action->handle($request->validated());
        $user = $result['user'];

        $activityLogger->log(
            'public.registered',
            'Creation de compte usager public.',
            $user,
            [
                'public_user_type_id' => $user->public_user_type_id,
                'phone' => $user->phone,
            ],
            $request,
            $user,
            'public',
        );

        return ApiResponse::success([
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'expires_in' => PublicApiTokenTtl::seconds(),
            'user' => new PublicUserResource($user->loadMissing(['publicUserType.pricingRule', 'countryReference', 'cityReference', 'communeReference'])),
        ], 'Compte public cree avec succes.', 201);
    }

    public function login(LoginPublicUserRequest $request, LoginPublicUserAction $action, ActivityLogger $activityLogger)
    {
        $result = $action->handle(
            $request->string('phone')->value(),
            $request->string('password')->value(),
        );
        $user = $result['user'];

        $activityLogger->log(
            'public.login',
            'Connexion usager public.',
            $user,
            [
                'phone' => $user->phone,
            ],
            $request,
            $user,
            'public',
        );

        return ApiResponse::success([
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'expires_in' => PublicApiTokenTtl::seconds(),
            'user' => new PublicUserResource($user->loadMissing(['publicUserType.pricingRule', 'countryReference', 'cityReference', 'communeReference'])),
        ], 'Connexion reussie.');
    }

    public function requestPasswordResetOtp(RequestPublicPasswordResetOtpRequest $request, RequestPublicOtpAction $action, ActivityLogger $activityLogger)
    {
        $result = $action->handle($request->string('phone')->value(), OtpPurpose::PasswordReset);

        $activityLogger->log(
            'public.password_reset_otp_requested',
            'Demande de code OTP UP pour reinitialisation du mot de passe.',
            'public_auth',
            [
                'phone' => $request->string('phone')->value(),
            ],
            $request,
            null,
            'public',
        );

        return ApiResponse::success([
            'phone' => $result->phone,
            'expires_at' => $result->expiresAt,
            'otp_code_for_testing' => app()->environment('local', 'testing') ? $result->code : null,
        ], 'OTP envoye avec succes.');
    }

    public function verifyPasswordResetOtp(VerifyPublicPasswordResetOtpRequest $request, VerifyPublicOtpAction $action, ActivityLogger $activityLogger)
    {
        $verification = $action->handle(
            $request->string('phone')->value(),
            $request->string('code')->value(),
            OtpPurpose::PasswordReset,
        );

        $activityLogger->log(
            'public.password_reset_otp_verified',
            'Verification OTP UP pour reinitialisation du mot de passe.',
            'public_auth',
            [
                'phone' => $verification->phone,
            ],
            $request,
            null,
            'public',
        );

        return ApiResponse::success([
            'phone' => $verification->phone,
            'verification_token' => $verification->token,
            'expires_at' => $verification->expires_at->toIso8601String(),
        ], 'Numero verifie avec succes.');
    }

    public function resetPassword(ResetPublicPasswordRequest $request, ResetPublicPasswordAction $action, ActivityLogger $activityLogger)
    {
        $user = $action->handle(
            $request->string('phone')->value(),
            $request->string('verification_token')->value(),
            $request->string('password')->value(),
        );

        $activityLogger->log(
            'public.password_reset_completed',
            'Reinitialisation du mot de passe UP.',
            $user,
            [
                'phone' => $user->phone,
            ],
            $request,
            $user,
            'public',
        );

        return ApiResponse::success([
            'user' => new PublicUserResource($user),
        ], 'Mot de passe reinitialise avec succes.');
    }
}
