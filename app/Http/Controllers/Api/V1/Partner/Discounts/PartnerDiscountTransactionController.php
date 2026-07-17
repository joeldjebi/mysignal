<?php

namespace App\Http\Controllers\Api\V1\Partner\Discounts;

use App\Domain\Discounts\Actions\ApplyPartnerDiscountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Discounts\StorePartnerDiscountTransactionRequest;
use App\Http\Resources\Api\V1\Partner\Discounts\PartnerDiscountTransactionResource;
use App\Models\PartnerDiscountTransaction;
use App\Models\User;
use App\Services\Notifications\PushNotificationDispatcher;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PartnerDiscountTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PartnerDiscountTransaction::query()
            ->with(['offer', 'discountCard', 'privilegeCard.type', 'partnerUser', 'publicUser'])
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

    public function store(
        StorePartnerDiscountTransactionRequest $request,
        ApplyPartnerDiscountAction $action,
        ActivityLogger $activityLogger,
        PushNotificationDispatcher $notifications,
    )
    {
        $partnerUser = $request->user('partner_api');
        $transaction = $action->handle($partnerUser, $request->validated());

        $activityLogger->log(
            'partner.discount_transaction.created',
            'Application d une reduction partenaire.',
            $transaction,
            [
                'organization_id' => $transaction->organization_id,
                'partner_user_id' => $transaction->partner_user_id,
                'offer_id' => $transaction->partner_discount_offer_id,
                'up_discount_card_id' => $transaction->up_discount_card_id,
                'privilege_card_id' => $transaction->privilege_card_id,
                'card_source' => $transaction->card_source,
                'scan_reference' => $transaction->scan_reference,
            ],
            $request,
            $partnerUser,
            'partner',
        );

        $this->sendDiscountNotifications($transaction, $partnerUser, $notifications);

        return ApiResponse::success([
            'transaction' => new PartnerDiscountTransactionResource($transaction),
        ], 'Reduction appliquee avec succes.', 201);
    }

    public function mobileHistory(Request $request)
    {
        $transactions = PartnerDiscountTransaction::query()
            ->with(['offer', 'discountCard', 'privilegeCard.type', 'partnerUser', 'publicUser'])
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

        $transactions = (clone $query)->get([
            'discount_amount',
            'final_amount',
            'original_amount',
            'discount_type_snapshot',
            'discount_value_snapshot',
            'applied_at',
        ]);

        $resolveDiscountAmount = static function (PartnerDiscountTransaction $transaction): float {
            if ($transaction->discount_amount !== null) {
                return (float) $transaction->discount_amount;
            }

            $type = strtolower((string) $transaction->discount_type_snapshot);
            $value = $transaction->discount_value_snapshot !== null ? (float) $transaction->discount_value_snapshot : null;
            $originalAmount = $transaction->original_amount !== null ? (float) $transaction->original_amount : null;

            if (in_array($type, ['fixed', 'fixed_amount', 'amount'], true) && $value !== null) {
                return $originalAmount !== null ? min($value, $originalAmount) : $value;
            }

            if (in_array($type, ['percentage', 'percent'], true) && $value !== null && $originalAmount !== null) {
                return $originalAmount * $value / 100;
            }

            if ($originalAmount !== null && $transaction->final_amount !== null) {
                return max(0, $originalAmount - (float) $transaction->final_amount);
            }

            return 0;
        };

        $resolveFinalAmount = static function (PartnerDiscountTransaction $transaction) use ($resolveDiscountAmount): float {
            if ($transaction->final_amount !== null) {
                return (float) $transaction->final_amount;
            }

            if ($transaction->original_amount === null) {
                return 0;
            }

            return max(0, (float) $transaction->original_amount - $resolveDiscountAmount($transaction));
        };

        $todayCount = (clone $query)
            ->whereDate('applied_at', now()->toDateString())
            ->count();

        return ApiResponse::success([
            'stats' => [
                'total_scans' => $transactions->count(),
                'today_scans' => $todayCount,
                'total_discount_amount' => (float) $transactions->sum($resolveDiscountAmount),
                'total_original_amount' => (float) $transactions->sum(fn ($transaction) => (float) ($transaction->original_amount ?? 0)),
                'total_final_amount' => (float) $transactions->sum($resolveFinalAmount),
                'last_scan_at' => $transactions->sortByDesc('applied_at')->first()?->applied_at?->toIso8601String(),
            ],
        ]);
    }

    private function sendDiscountNotifications(PartnerDiscountTransaction $transaction, User $partnerUser, PushNotificationDispatcher $notifications): void
    {
        try {
            $transaction->loadMissing(['offer', 'discountCard', 'privilegeCard.type', 'partnerUser', 'publicUser', 'organization']);

            $offerName = (string) ($transaction->offer?->name ?? $transaction->privilegeCard?->type?->name ?? 'remise partenaire');
            $discountAmount = $this->formatAmount($transaction->discount_amount, $transaction->offer?->currency ?? $transaction->privilegeCard?->type?->currency);
            $publicUserName = trim((string) ($transaction->publicUser?->first_name.' '.$transaction->publicUser?->last_name));
            $publicUserLabel = $publicUserName !== '' ? $publicUserName : 'UP';

            $payload = [
                'category' => 'discount',
                'source' => 'partner_discount',
                'screen' => 'notifications',
                'transaction_id' => (string) $transaction->id,
                'scan_reference' => (string) $transaction->scan_reference,
                'offer_id' => (string) $transaction->partner_discount_offer_id,
                'offer_name' => $offerName,
                'discount_card_id' => (string) $transaction->up_discount_card_id,
                'privilege_card_id' => (string) $transaction->privilege_card_id,
                'card_source' => (string) ($transaction->card_source ?? 'up_discount_card'),
                'public_user_id' => (string) $transaction->public_user_id,
                'organization_id' => (string) $transaction->organization_id,
                'original_amount' => (string) ($transaction->original_amount ?? ''),
                'discount_amount' => (string) ($transaction->discount_amount ?? ''),
                'final_amount' => (string) ($transaction->final_amount ?? ''),
                'currency' => (string) ($transaction->offer?->currency ?? $transaction->privilegeCard?->type?->currency ?? ''),
            ];

            $notifications->notifyPartnerUser(
                $partnerUser,
                'partner_discount_applied',
                'Remise appliquée',
                "La remise {$offerName} a été appliquée pour {$publicUserLabel}.",
                [
                    ...$payload,
                    'recipient_role' => 'partner_scanner',
                ],
            );

            if ($transaction->publicUser !== null) {
                $body = $discountAmount !== null
                    ? "Une remise de {$discountAmount} vient d'être appliquée à votre compte."
                    : "Une remise vient d'être appliquée à votre compte.";

                $notifications->notifyPublicUser(
                    $transaction->publicUser,
                    'public_discount_received',
                    'Remise reçue',
                    $body,
                    [
                        ...$payload,
                        'recipient_role' => 'public_user',
                        'screen' => 'dashboard',
                    ],
                );
            }
        } catch (\Throwable $exception) {
            Log::warning('Unable to create discount notifications.', [
                'transaction_id' => $transaction->id,
                'partner_user_id' => $partnerUser?->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function formatAmount($amount, ?string $currency): ?string
    {
        if ($amount === null) {
            return null;
        }

        $formatted = number_format((float) $amount, 0, ',', ' ');
        $currency = $currency !== null && $currency !== '' ? $currency : 'FCFA';

        return "{$formatted} {$currency}";
    }
}
