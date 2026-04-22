<?php

namespace App\Http\Controllers\Api\V1\Partner\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Profile\UpdatePartnerProfileRequest;
use App\Http\Resources\Api\V1\Partner\Auth\PartnerUserResource;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;

class PartnerProfileController extends Controller
{
    public function update(UpdatePartnerProfileRequest $request, ActivityLogger $activityLogger)
    {
        $user = $request->user('partner_api');

        $user->update($request->validated());
        $user->loadMissing(['organization.organizationType', 'roles.permissions']);

        $activityLogger->log(
            'partner.profile.updated',
            'Mise a jour du profil partenaire.',
            $user,
            [
                'organization_id' => $user->organization_id,
                'phone' => $user->phone,
                'email' => $user->email,
            ],
            $request,
            $user,
            'partner',
        );

        return ApiResponse::success([
            'user' => new PartnerUserResource($user),
        ], 'Profil partenaire mis a jour.');
    }
}
