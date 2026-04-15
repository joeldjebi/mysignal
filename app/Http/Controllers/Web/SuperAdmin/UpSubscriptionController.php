<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UpSubscription;
use Illuminate\Contracts\View\View;

class UpSubscriptionController extends Controller
{
    public function index(): View
    {
        $query = UpSubscription::query()
            ->with([
                'publicUser.publicUserType',
                'plan',
                'payments',
            ]);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->whereHas('publicUser', function ($publicUserQuery) use ($search): void {
                    $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('company_name', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('plan', function ($planQuery) use ($search): void {
                        $planQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('code', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('payments', function ($paymentQuery) use ($search): void {
                        $paymentQuery->where('reference', 'like', '%'.$search.'%')
                            ->orWhere('provider_reference', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('subscription_plan_id'))) {
            $query->where('subscription_plan_id', (int) request('subscription_plan_id'));
        }

        if (filled(request('payment_status'))) {
            $query->whereHas('payments', fn ($paymentQuery) => $paymentQuery->where('status', request('payment_status')));
        }

        return view('super-admin.up-subscriptions.index', [
            'subscriptions' => $query->latest('id')->paginate(15)->withQueryString(),
            'plans' => SubscriptionPlan::query()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }
}
