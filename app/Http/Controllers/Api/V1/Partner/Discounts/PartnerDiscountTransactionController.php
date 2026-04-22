<?php

namespace App\Http\Controllers\Api\V1\Partner\Discounts;

use App\Domain\Discounts\Actions\ApplyPartnerDiscountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Discounts\StorePartnerDiscountTransactionRequest;
use App\Http\Resources\Api\V1\Partner\Discounts\PartnerDiscountTransactionResource;
use App\Models\PartnerDiscountTransaction;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;

class PartnerDiscountTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PartnerDiscountTransaction::query()
            ->with(['offer', 'discountCard', 'partnerUser', 'publicUser'])
            ->where('organization_id', $request->user('partner_api')->organization_id);

        if ($request->filled('partner_user_id')) {
            $query->where('partner_user_id', $request->integer('partner_user_id'));
        }

        if ($request->filled('offer_id')) {
            $query->where('partner_discount_offer_id', $request->integer('offer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('applied_at', '>=', $request->date('date_from')->toDateString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('applied_at', '<=', $request->date('date_to')->toDateString());
        }

        $transactions = $query->latest('id')->paginate(20)->withQueryString();

        return ApiResponse::success([
            'transactions' => PartnerDiscountTransactionResource::collection($transactions->getCollection()),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function store(StorePartnerDiscountTransactionRequest $request, ApplyPartnerDiscountAction $action, ActivityLogger $activityLogger)
    {
        $transaction = $action->handle($request->user('partner_api'), $request->validated());

        $activityLogger->log(
            'partner.discount_transaction.created',
            'Application d une reduction partenaire.',
            $transaction,
            [
                'organization_id' => $transaction->organization_id,
                'partner_user_id' => $transaction->partner_user_id,
                'offer_id' => $transaction->partner_discount_offer_id,
                'up_discount_card_id' => $transaction->up_discount_card_id,
                'scan_reference' => $transaction->scan_reference,
            ],
            $request,
            $request->user('partner_api'),
            'partner',
        );

        return ApiResponse::success([
            'transaction' => new PartnerDiscountTransactionResource($transaction),
        ], 'Reduction appliquee avec succes.', 201);
    }

    public function mobileHistory(Request $request)
    {
        $transactions = PartnerDiscountTransaction::query()
            ->with(['offer', 'discountCard', 'partnerUser', 'publicUser'])
            ->where('partner_user_id', $request->user('partner_api')->id)
            ->latest('id')
            ->limit(20)
            ->get();

        return ApiResponse::success([
            'transactions' => PartnerDiscountTransactionResource::collection($transactions),
        ]);
    }

    public function mobileStats(Request $request)
    {
        $query = PartnerDiscountTransaction::query()
            ->where('partner_user_id', $request->user('partner_api')->id)
            ->where('status', 'validated');

        if ($request->filled('date_from')) {
            $query->whereDate('applied_at', '>=', $request->date('date_from')->toDateString());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('applied_at', '<=', $request->date('date_to')->toDateString());
        }

        $transactions = (clone $query)->get(['discount_amount', 'final_amount', 'original_amount', 'applied_at']);

        $todayCount = (clone $query)
            ->whereDate('applied_at', now()->toDateString())
            ->count();

        return ApiResponse::success([
            'stats' => [
                'total_scans' => $transactions->count(),
                'today_scans' => $todayCount,
                'total_discount_amount' => (float) $transactions->sum(fn ($transaction) => (float) ($transaction->discount_amount ?? 0)),
                'total_original_amount' => (float) $transactions->sum(fn ($transaction) => (float) ($transaction->original_amount ?? 0)),
                'total_final_amount' => (float) $transactions->sum(fn ($transaction) => (float) ($transaction->final_amount ?? 0)),
                'last_scan_at' => $transactions->sortByDesc('applied_at')->first()?->applied_at?->toIso8601String(),
            ],
        ]);
    }
}
