<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UpDiscountCard;
use Illuminate\Contracts\View\View;

class DiscountCardController extends Controller
{
    public function index(): View
    {
        $query = UpDiscountCard::query()
            ->with(['publicUser.publicUserType', 'subscription.plan']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('card_number', 'like', '%'.$search.'%')
                    ->orWhere('card_uuid', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('subscription_status'))) {
            $query->whereHas('subscription', fn ($subscriptionQuery) => $subscriptionQuery->where('status', request('subscription_status')));
        }

        if (filled(request('public_user_type_id'))) {
            $typeId = (int) request('public_user_type_id');
            $query->whereHas('publicUser', fn ($publicUserQuery) => $publicUserQuery->where('public_user_type_id', $typeId));
        }

        if (filled(request('date_from'))) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (filled(request('date_to'))) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        if (filled(request('expires_from'))) {
            $query->whereDate('expires_at', '>=', request('expires_from'));
        }

        if (filled(request('expires_to'))) {
            $query->whereDate('expires_at', '<=', request('expires_to'));
        }

        return view('super-admin.discount-cards.index', [
            'cards' => $query->latest('id')->paginate(15)->withQueryString(),
            'publicUserTypes' => \App\Models\PublicUserType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
