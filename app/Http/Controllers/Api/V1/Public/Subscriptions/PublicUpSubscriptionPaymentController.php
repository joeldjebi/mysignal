<?php

namespace App\Http\Controllers\Api\V1\Public\Subscriptions;

use App\Domain\Subscriptions\Actions\ConfirmUpSubscriptionPaymentAction;
use App\Domain\Subscriptions\Actions\CreateUpSubscriptionPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Subscriptions\SubscriptionPaymentResource;
use App\Http\Resources\Api\V1\Public\Subscriptions\UpSubscriptionResource;
use App\Models\SubscriptionPayment;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;

class PublicUpSubscriptionPaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = SubscriptionPayment::query()
            ->with('subscription.plan')
            ->where('public_user_id', $request->user('public_api')->id)
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'payments' => SubscriptionPaymentResource::collection($payments),
        ]);
    }

    public function store(Request $request, CreateUpSubscriptionPaymentAction $action, ActivityLogger $activityLogger)
    {
        $payment = $action->handle($request->user('public_api'));
        $payment->load('subscription.plan');

        $activityLogger->log(
            'public.subscription_payment.created',
            'Initialisation d un paiement d abonnement UP.',
            $payment,
            [
                'reference' => $payment->reference,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'up_subscription_id' => $payment->up_subscription_id,
            ],
            $request
        );

        return ApiResponse::success([
            'payment' => new SubscriptionPaymentResource($payment),
        ], 'Paiement d abonnement initialise avec succes.', 201);
    }

    public function confirm(Request $request, SubscriptionPayment $payment, ConfirmUpSubscriptionPaymentAction $action, ActivityLogger $activityLogger)
    {
        $payment = $action->handle($request->user('public_api'), $payment);

        $activityLogger->log(
            'public.subscription_payment.confirmed',
            'Confirmation d un paiement d abonnement UP.',
            $payment,
            [
                'reference' => $payment->reference,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'up_subscription_id' => $payment->up_subscription_id,
            ],
            $request
        );

        return ApiResponse::success([
            'payment' => new SubscriptionPaymentResource($payment),
            'subscription' => new UpSubscriptionResource($payment->subscription),
        ], 'Paiement d abonnement confirme avec succes.');
    }
}
