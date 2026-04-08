<?php

namespace App\Http\Controllers\Api\V1\Public\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Auth\PublicUserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class AuthenticatedPublicUserController extends Controller
{
    public function __invoke(Request $request)
    {
        return ApiResponse::success([
            'user' => new PublicUserResource($request->user('public_api')->loadMissing('publicUserType.pricingRule')),
        ]);
    }
}
