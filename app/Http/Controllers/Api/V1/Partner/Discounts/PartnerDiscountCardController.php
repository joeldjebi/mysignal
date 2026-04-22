<?php

namespace App\Http\Controllers\Api\V1\Partner\Discounts;

use App\Domain\Discounts\Actions\VerifyPartnerDiscountCardAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Discounts\VerifyPartnerDiscountCardRequest;
use App\Http\Resources\Api\V1\Partner\Discounts\PartnerDiscountOfferResource;
use App\Support\Api\ApiResponse;

class PartnerDiscountCardController extends Controller
{
    public function verify(VerifyPartnerDiscountCardRequest $request, VerifyPartnerDiscountCardAction $action)
    {
        $result = $action->handle(
            $request->user('partner_api'),
            $request->string('card_uuid')->value(),
            $request->integer('offer_id') ?: null,
        );

        return ApiResponse::success([
            'is_valid' => true,
            'message' => $result['message'],
            'verified_at' => $result['verified_at'],
            'subscription_status' => $result['subscription_status'],
            'member_display_name' => $result['member_display_name'],
            'card' => [
                'id' => $result['card']->id,
                'card_number' => $result['card']->card_number,
                'card_uuid' => $result['card']->card_uuid,
                'status' => $result['card']->status,
                'expires_at' => $result['card']->expires_at?->toIso8601String(),
            ],
            'offer' => $result['offer'] ? new PartnerDiscountOfferResource($result['offer']) : null,
        ]);
    }
}
