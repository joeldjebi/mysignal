<?php

namespace App\Http\Controllers\Api\V1\Partner\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Profile\UpdatePartnerPasswordRequest;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PartnerPasswordController extends Controller
{
    public function update(UpdatePartnerPasswordRequest $request, ActivityLogger $activityLogger)
    {
        $user = $request->user('partner_api');

        if (! Hash::check($request->string('current_password')->value(), (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est invalide.'],
            ]);
        }

        $user->update([
            'password' => $request->string('password')->value(),
        ]);

        $activityLogger->log(
            'partner.password.updated',
            'Mise a jour du mot de passe partenaire.',
            $user,
            [
                'organization_id' => $user->organization_id,
            ],
            $request,
            $user,
            'partner',
        );

        return ApiResponse::success([], 'Mot de passe mis a jour avec succes.');
    }
}
