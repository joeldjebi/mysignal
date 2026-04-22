<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PartnerDiscountOffer;
use App\Models\PartnerDiscountTransaction;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DiscountTransactionController extends Controller
{
    public function index(): View
    {
        $query = PartnerDiscountTransaction::query()
            ->with(['organization', 'partnerUser', 'offer', 'discountCard', 'publicUser.publicUserType', 'subscription.plan']);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('scan_reference', 'like', '%'.$search.'%')
                    ->orWhereHas('discountCard', function ($cardQuery) use ($search): void {
                        $cardQuery->where('card_number', 'like', '%'.$search.'%')
                            ->orWhere('card_uuid', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('partnerUser', function ($userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('offer', function ($offerQuery) use ($search): void {
                        $offerQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('code', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('verification_status'))) {
            $query->where('verification_status', request('verification_status'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', (int) request('organization_id'));
        }

        if (filled(request('partner_user_id'))) {
            $query->where('partner_user_id', (int) request('partner_user_id'));
        }

        if (filled(request('offer_id'))) {
            $query->where('partner_discount_offer_id', (int) request('offer_id'));
        }

        if (filled(request('public_user_type_id'))) {
            $typeId = (int) request('public_user_type_id');
            $query->whereHas('publicUser', fn ($publicUserQuery) => $publicUserQuery->where('public_user_type_id', $typeId));
        }

        if (filled(request('date_from'))) {
            $query->whereDate('applied_at', '>=', request('date_from'));
        }

        if (filled(request('date_to'))) {
            $query->whereDate('applied_at', '<=', request('date_to'));
        }

        $transactions = $query->latest('applied_at')->latest('id')->paginate(15)->withQueryString();

        return view('super-admin.discount-transactions.index', [
            'transactions' => $transactions,
            'organizations' => \App\Models\Organization::query()
                ->whereHas('partnerDiscountTransactions')
                ->whereHas('organizationType', fn ($typeQuery) => $typeQuery->where('code', 'PARTNER_ESTABLISHMENT'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'partnerUsers' => User::query()
                ->whereHas('partnerDiscountTransactions')
                ->orderBy('name')
                ->get(['id', 'name', 'organization_id']),
            'offers' => PartnerDiscountOffer::query()
                ->whereHas('transactions')
                ->orderBy('name')
                ->get(['id', 'name', 'organization_id']),
            'publicUserTypes' => \App\Models\PublicUserType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
