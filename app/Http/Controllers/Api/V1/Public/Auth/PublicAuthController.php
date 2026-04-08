<?php

namespace App\Http\Controllers\Api\V1\Public\Auth;

use App\Domain\Auth\Actions\LoginPublicUserAction;
use App\Domain\Auth\Actions\RegisterPublicUserAction;
use App\Domain\Auth\Actions\RequestPublicOtpAction;
use App\Domain\Auth\Actions\VerifyPublicOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Auth\LoginPublicUserRequest;
use App\Http\Requests\Api\V1\Public\Auth\RegisterPublicUserRequest;
use App\Http\Requests\Api\V1\Public\Auth\RequestOtpRequest;
use App\Http\Requests\Api\V1\Public\Auth\VerifyOtpRequest;
use App\Http\Resources\Api\V1\Public\Auth\PublicUserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Support\Facades\Auth;

class PublicAuthController extends Controller
{
    public function requestOtp(RequestOtpRequest $request, RequestPublicOtpAction $action)
    {
        $result = $action->handle($request->string('phone')->value());

        return ApiResponse::success([
            'phone' => $result->phone,
            'expires_at' => $result->expiresAt,
            'otp_code_for_testing' => app()->environment('local', 'testing') ? $result->code : null,
        ], 'OTP envoye avec succes.');
    }

    public function verifyOtp(VerifyOtpRequest $request, VerifyPublicOtpAction $action)
    {
        $verification = $action->handle(
            $request->string('phone')->value(),
            $request->string('code')->value(),
        );

        return ApiResponse::success([
            'phone' => $verification->phone,
            'verification_token' => $verification->token,
            'expires_at' => $verification->expires_at->toIso8601String(),
        ], 'Numero verifie avec succes.');
    }

    public function register(RegisterPublicUserRequest $request, RegisterPublicUserAction $action)
    {
        $result = $action->handle($request->validated());

        return ApiResponse::success([
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('public_api')->factory()->getTTL() * 60,
            'user' => new PublicUserResource($result['user']->loadMissing('publicUserType.pricingRule')),
        ], 'Compte public cree avec succes.', 201);
    }

    public function login(LoginPublicUserRequest $request, LoginPublicUserAction $action)
    {
        $result = $action->handle(
            $request->string('phone')->value(),
            $request->string('password')->value(),
        );

        return ApiResponse::success([
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('public_api')->factory()->getTTL() * 60,
            'user' => new PublicUserResource($result['user']->loadMissing('publicUserType.pricingRule')),
        ], 'Connexion reussie.');
    }
}
