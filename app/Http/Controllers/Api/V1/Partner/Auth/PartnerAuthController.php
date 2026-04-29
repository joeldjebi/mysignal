<?php

namespace App\Http\Controllers\Api\V1\Partner\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Auth\PartnerLoginRequest;
use App\Http\Resources\Api\V1\Partner\Auth\PartnerUserResource;
use App\Models\User;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use App\Support\Auth\PartnerAccessResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PartnerAuthController extends Controller
{
    public function login(PartnerLoginRequest $request, ActivityLogger $activityLogger, PartnerAccessResolver $partnerAccessResolver)
    {
        $identifierColumn = $request->filled('phone') ? 'phone' : 'email';
        $identifierValue = $request->filled('phone')
            ? $request->string('phone')->value()
            : $request->string('email')->value();
        $password = $request->string('password')->value();

        $matchedPassword = false;
        $user = null;
        $partnerAccess = null;

        User::query()
            ->with(['creator', 'permissions', 'roles.permissions', 'organization.organizationType'])
            ->where($identifierColumn, $identifierValue)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->each(function (User $candidate) use ($password, $partnerAccessResolver, &$matchedPassword, &$user, &$partnerAccess): bool {
                if (! Hash::check($password, $candidate->password)) {
                    return true;
                }

                $matchedPassword = true;

                if ($candidate->is_super_admin) {
                    return true;
                }

                $resolvedAccess = $partnerAccessResolver->resolve($candidate);

                if ($resolvedAccess === null) {
                    return true;
                }

                $partnerAccessResolver->apply($candidate, $resolvedAccess);

                if (! $candidate->creator?->is_super_admin && ! $candidate->hasEffectivePermissionCode('PARTNER_ACCESS_PORTAL')) {
                    return true;
                }

                $user = $candidate;
                $partnerAccess = $resolvedAccess;

                return false;
            });

        if (! $matchedPassword) {
            throw ValidationException::withMessages([
                $request->filled('phone') ? 'phone' : 'email' => ['Identifiants invalides.'],
            ]);
        }

        if (! $user instanceof User || $partnerAccess === null) {
            throw ValidationException::withMessages([
                $request->filled('phone') ? 'phone' : 'email' => ['Ce compte n est pas autorise a acceder au portail partenaire.'],
            ]);
        }

        Auth::guard('partner_api')->setUser($user);

        $token = JWTAuth::claims([
            'guard' => 'partner_api',
            'portal' => 'partner',
            'access_id' => $partnerAccess->exists ? $partnerAccess->id : null,
            'organization_id' => $partnerAccess->organization_id,
            'is_super_admin' => false,
        ])->fromUser($user);

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
