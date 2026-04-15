<?php

namespace App\Http\Controllers\Api\V1\Public\Subscriptions;

use App\Domain\Subscriptions\Actions\CreateUpSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\Subscriptions\UpSubscriptionResource;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;

class PublicUpSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = $request->user('public_api')
            ->subscriptions()
            ->with(['plan', 'payments'])
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'subscriptions' => UpSubscriptionResource::collection($subscriptions),
        ]);
    }

    public function show(Request $request)
    {
        $subscription = $request->user('public_api')
            ->subscriptions()
            ->with(['plan', 'payments'])
            ->latest('id')
            ->first();

        return ApiResponse::success([
            'subscription' => $subscription ? new UpSubscriptionResource($subscription) : null,
        ]);
    }

    public function store(Request $request, CreateUpSubscriptionAction $action, ActivityLogger $activityLogger)
    {
        $subscription = $action->handle($request->user('public_api'));
        $subscription->load('plan');

        $activityLogger->log(
            'public.subscription.created',
            'Initialisation d un abonnement UP.',
            $subscription,
            [
                'status' => $subscription->status,
                'amount' => $subscription->amount,
                'currency' => $subscription->currency,
                'subscription_plan_id' => $subscription->subscription_plan_id,
            ],
            $request
        );

        return ApiResponse::success([
            'subscription' => new UpSubscriptionResource($subscription),
        ], 'Abonnement initialise avec succes.', 201);
    }
}
