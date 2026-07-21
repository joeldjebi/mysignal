<?php

namespace App\Http\Controllers\Api\V1\Public\PrivilegeCards;

use App\Domain\PrivilegeCards\Actions\InitiatePrivilegeCardFineoPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\PrivilegeCards\PrivilegeCardPaymentSessionResource;
use App\Http\Resources\Api\V1\Public\PrivilegeCards\PrivilegeCardResource;
use App\Http\Resources\Api\V1\Public\PrivilegeCards\PrivilegeCardTypeResource;
use App\Models\PrivilegeCardPaymentSession;
use App\Models\PrivilegeCardType;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicPrivilegeCardController extends Controller
{
    public function index()
    {
        $types = PrivilegeCardType::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return ApiResponse::success([
            'cards' => PrivilegeCardTypeResource::collection($types),
        ]);
    }

    public function mine(Request $request)
    {
        $card = $request->user('public_api')
            ->privilegeCards()
            ->with('type')
            ->latest('id')
            ->first();

        return ApiResponse::success([
            'card' => $card ? new PrivilegeCardResource($card) : null,
        ]);
    }

    public function sessions(Request $request)
    {
        $sessions = PrivilegeCardPaymentSession::query()
            ->with(['type', 'card.type'])
            ->where('public_user_id', $request->user('public_api')->id)
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'payment_sessions' => PrivilegeCardPaymentSessionResource::collection($sessions),
        ]);
    }

    public function purchase(Request $request, PrivilegeCardType $type, InitiatePrivilegeCardFineoPaymentAction $action)
    {
        $session = $action->handle($request->user('public_api'), $type);

        return ApiResponse::success([
            'payment_session' => new PrivilegeCardPaymentSessionResource($session),
            'checkout_link' => $session->checkout_link,
        ], 'Lien de paiement carte privilege genere avec succes.');
    }

    public function session(Request $request, string $syncRef)
    {
        $session = PrivilegeCardPaymentSession::query()
            ->with(['type', 'card.type'])
            ->where('public_user_id', $request->user('public_api')->id)
            ->where('sync_ref', $syncRef)
            ->firstOrFail();

        return ApiResponse::success([
            'payment_session' => new PrivilegeCardPaymentSessionResource($session),
        ]);
    }
}
