<?php

namespace App\Http\Controllers\Api\V1\Public\Profile;

use App\Domain\PublicUsers\Actions\UpdatePublicProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Profile\UpdatePublicProfileRequest;
use App\Http\Resources\Api\V1\Public\Auth\PublicUserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(Request $request)
    {
        return ApiResponse::success([
            'user' => new PublicUserResource($request->user('public_api')->loadMissing('publicUserType.pricingRule')),
        ]);
    }

    public function update(UpdatePublicProfileRequest $request, UpdatePublicProfileAction $action)
    {
        $user = $action->handle($request->user('public_api'), $request->validated());

        return ApiResponse::success([
            'user' => new PublicUserResource($user->loadMissing('publicUserType.pricingRule')),
        ], 'Profil mis a jour avec succes.');
    }
}
