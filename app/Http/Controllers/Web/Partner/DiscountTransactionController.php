<?php

namespace App\Http\Controllers\Web\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Partner\Concerns\InteractsWithPartnerContext;
use App\Models\PartnerDiscountOffer;
use App\Models\PartnerDiscountTransaction;
use App\Models\User;
use Illuminate\View\View;

class DiscountTransactionController extends Controller
{
    use InteractsWithPartnerContext;

    public function index(): View
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);

        $query = PartnerDiscountTransaction::query()
            ->with(['offer', 'partnerUser', 'publicUser', 'discountCard'])
            ->where('organization_id', $organization->id);

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
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
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

        if (filled(request('partner_user_id'))) {
            $query->where('partner_user_id', (int) request('partner_user_id'));
        }

        if (filled(request('offer_id'))) {
            $query->where('partner_discount_offer_id', (int) request('offer_id'));
        }

        if (filled(request('date_from'))) {
            $query->whereDate('applied_at', '>=', request('date_from'));
        }

        if (filled(request('date_to'))) {
            $query->whereDate('applied_at', '<=', request('date_to'));
        }

        return view('partner.discount-transactions.index', [
            'organization' => $organization,
            'activeNav' => 'transactions',
            'authorization' => $this->partnerAuthorizationFlags(),
            'transactions' => $query->latest('applied_at')->latest('id')->paginate(15)->withQueryString(),
            'partnerUsers' => User::query()
                ->where('organization_id', $organization->id)
                ->where('is_super_admin', false)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'offers' => PartnerDiscountOffer::query()
                ->where('organization_id', $organization->id)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ]);
    }
}
