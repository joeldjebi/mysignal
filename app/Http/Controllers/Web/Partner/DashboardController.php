<?php

namespace App\Http\Controllers\Web\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Partner\Concerns\InteractsWithPartnerContext;
use App\Models\PartnerDiscountOffer;
use App\Models\PartnerDiscountTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use InteractsWithPartnerContext;

    public function __invoke(): View
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);

        $authorization = $this->partnerAuthorizationFlags();
        $query = PartnerDiscountTransaction::query()
            ->where('organization_id', $organization->id);

        if (filled(request('date_from'))) {
            $query->whereDate('applied_at', '>=', request('date_from'));
        }

        if (filled(request('date_to'))) {
            $query->whereDate('applied_at', '<=', request('date_to'));
        }

        $statsQuery = clone $query;
        $transactions = (clone $query)
            ->with(['offer', 'partnerUser', 'publicUser', 'discountCard'])
            ->latest('id')
            ->take(10)
            ->get();

        $topAgents = PartnerDiscountTransaction::query()
            ->select('partner_user_id', DB::raw('COUNT(*) as total'))
            ->with('partnerUser')
            ->where('organization_id', $organization->id)
            ->groupBy('partner_user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topOffers = PartnerDiscountTransaction::query()
            ->select('partner_discount_offer_id', DB::raw('COUNT(*) as total'))
            ->with('offer')
            ->where('organization_id', $organization->id)
            ->groupBy('partner_discount_offer_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('partner.dashboard', [
            'organization' => $organization,
            'activeNav' => 'dashboard',
            'authorization' => $authorization,
            'stats' => [
                'transactions' => (clone $statsQuery)->count(),
                'validated_transactions' => (clone $statsQuery)->where('status', 'validated')->count(),
                'offers' => PartnerDiscountOffer::query()->where('organization_id', $organization->id)->count(),
                'active_offers' => PartnerDiscountOffer::query()->where('organization_id', $organization->id)->where('status', 'active')->count(),
                'users' => User::query()->where('organization_id', $organization->id)->where('is_super_admin', false)->count(),
                'active_users' => User::query()->where('organization_id', $organization->id)->where('is_super_admin', false)->where('status', 'active')->count(),
            ],
            'recentTransactions' => $transactions,
            'topAgents' => $topAgents,
            'topOffers' => $topOffers,
        ]);
    }
}
