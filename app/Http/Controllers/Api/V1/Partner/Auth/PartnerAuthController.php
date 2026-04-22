<?php

namespace App\Http\Controllers\Api\V1\Partner\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Auth\PartnerLoginRequest;
use App\Http\Resources\Api\V1\Partner\Auth\PartnerUserResource;
use App\Models\User;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PartnerAuthController extends Controller
{
    public function login(PartnerLoginRequest $request, ActivityLogger $activityLogger)
    {
        $credentials = [
            ($request->filled('phone') ? 'phone' : 'email') => $request->filled('phone')
                ? $request->string('phone')->value()
                : $request->string('email')->value(),
            'password' => $request->string('password')->value(),
        ];

        if (! $token = Auth::guard('partner_api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                $request->filled('phone') ? 'phone' : 'email' => ['Identifiants invalides.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::guard('partner_api')->user()->loadMissing(['organization.organizationType', 'roles.permissions']);

        if (
            $user->status !== 'active' ||
            $user->is_super_admin ||
            $user->organization_id === null ||
            $user->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT'
        ) {
            Auth::guard('partner_api')->logout();

            throw ValidationException::withMessages([
                $request->filled('phone') ? 'phone' : 'email' => ['Ce compte n est pas autorise a acceder au portail partenaire.'],
            ]);
        }

        $activityLogger->log(
            'partner.login',
            'Connexion partenaire.',
            $user,
            [
                'organization_id' => $user->organization_id,
                'email' => $user->email,
            ],
            $request,
            $user,
            'partner',
        );

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('partner_api')->factory()->getTTL() * 60,
            'user' => new PartnerUserResource($user),
        ], 'Connexion partenaire reussie.');
    }

    public function logout(Request $request, ActivityLogger $activityLogger)
    {
        $user = $request->user('partner_api');

        if ($user instanceof User) {
            $activityLogger->log(
                'partner.logout',
                'Deconnexion partenaire.',
                $user,
                [
                    'organization_id' => $user->organization_id,
                ],
                $request,
                $user,
                'partner',
            );
        }

        Auth::guard('partner_api')->logout();

        return ApiResponse::success([], 'Deconnexion effectuee.');
    }
}
