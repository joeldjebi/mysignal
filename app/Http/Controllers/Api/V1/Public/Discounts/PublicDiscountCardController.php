<?php

namespace App\Http\Controllers\Api\V1\Public\Discounts;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Discounts\UpDiscountCardResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicDiscountCardController extends Controller
{
    public function show(Request $request)
    {
        $card = $request->user('public_api')
            ->discountCards()
            ->with('subscription.plan')
            ->latest('id')
            ->first();

        return ApiResponse::success([
            'card' => $card ? new UpDiscountCardResource($card) : null,
        ]);
    }
}
