<?php

namespace App\Http\Controllers\Api\V1\Public\UserTypes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\UserTypes\PublicUserTypeResource;
use App\Models\PublicUserType;
use App\Support\Api\ApiResponse;

class PublicUserTypeController extends Controller
{
    public function index()
    {
        $userTypes = PublicUserType::query()
            ->with('pricingRule')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            'user_types' => PublicUserTypeResource::collection($userTypes),
        ]);
    }
}
