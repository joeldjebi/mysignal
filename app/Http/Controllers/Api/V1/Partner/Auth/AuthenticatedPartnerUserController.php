<?php

namespace App\Http\Controllers\Api\V1\Partner\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Partner\Auth\PartnerUserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class AuthenticatedPartnerUserController extends Controller
{
    public function __invoke(Request $request)
    {
        return ApiResponse::success([
            'user' => new PartnerUserResource($request->user('partner_api')->loadMissing(['organization.organizationType', 'roles.permissions'])),
        ]);
    }
}
