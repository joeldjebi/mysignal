<?php

namespace App\Http\Controllers\Web\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Partner\Concerns\InteractsWithPartnerContext;
use App\Models\PartnerDiscountOffer;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscountOfferController extends Controller
{
    use InteractsWithPartnerContext;

    public function index(): View
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);

        $query = PartnerDiscountOffer::query()
            ->where('organization_id', $organization->id);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('partner.offers.index', [
            'organization' => $organization,
            'activeNav' => 'offers',
            'authorization' => $this->partnerAuthorizationFlags(),
            'offers' => $query->latest('id')->paginate(12)->withQueryString(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);

        $attributes = $this->validateRequest($request);

        $offer = PartnerDiscountOffer::query()->create([
            'organization_id' => $organization->id,
            'code' => $attributes['code'],
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'discount_type' => $attributes['discount_type'],
            'discount_value' => $attributes['discount_value'] ?? null,
            'currency' => $attributes['currency'] ?? null,
            'minimum_purchase_amount' => $attributes['minimum_purchase_amount'] ?? null,
            'maximum_discount_amount' => $attributes['maximum_discount_amount'] ?? null,
            'max_uses_per_card' => $attributes['max_uses_per_card'] ?? null,
            'max_uses_per_day' => $attributes['max_uses_per_day'] ?? null,
            'starts_at' => $attributes['starts_at'] ?? null,
            'ends_at' => $attributes['ends_at'] ?? null,
            'status' => $attributes['status'] ?? 'draft',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $activityLogger->log(
            'partner.web.discount_offer.created',
            'Creation d une offre partenaire depuis le dashboard web.',
            $offer,
            [
                'organization_id' => $organization->id,
                'code' => $offer->code,
            ],
            $request,
            $request->user(),
            'partner',
        );

        return redirect()->route('partner.offers.index')->with('success', 'L offre de reduction a ete creee.');
    }

    public function edit(PartnerDiscountOffer $offer): View
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null || $offer->organization_id !== $organization->id, 404);

        return view('partner.offers.edit', [
            'organization' => $organization,
            'activeNav' => 'offers',
            'authorization' => $this->partnerAuthorizationFlags(),
            'offer' => $offer,
        ]);
    }

    public function update(Request $request, PartnerDiscountOffer $offer, ActivityLogger $activityLogger): RedirectResponse
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null || $offer->organization_id !== $organization->id, 404);

        $attributes = $this->validateRequest($request, $offer);

        $offer->update([
            'code' => $attributes['code'],
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'discount_type' => $attributes['discount_type'],
            'discount_value' => $attributes['discount_value'] ?? null,
            'currency' => $attributes['currency'] ?? null,
            'minimum_purchase_amount' => $attributes['minimum_purchase_amount'] ?? null,
            'maximum_discount_amount' => $attributes['maximum_discount_amount'] ?? null,
            'max_uses_per_card' => $attributes['max_uses_per_card'] ?? null,
            'max_uses_per_day' => $attributes['max_uses_per_day'] ?? null,
            'starts_at' => $attributes['starts_at'] ?? null,
            'ends_at' => $attributes['ends_at'] ?? null,
            'status' => $attributes['status'],
            'updated_by' => $request->user()->id,
        ]);

        $activityLogger->log(
            'partner.web.discount_offer.updated',
            'Mise a jour d une offre partenaire depuis le dashboard web.',
            $offer,
            [
                'organization_id' => $organization->id,
                'code' => $offer->code,
                'status' => $offer->status,
            ],
            $request,
            $request->user(),
            'partner',
        );

        return redirect()->route('partner.offers.index')->with('success', 'L offre de reduction a ete mise a jour.');
    }

    public function toggleStatus(Request $request, PartnerDiscountOffer $offer, ActivityLogger $activityLogger): RedirectResponse
    {
        $context = $this->partnerContext();
        $organization = $context['organization'];
        abort_if($organization === null || $offer->organization_id !== $organization->id, 404);

        $offer->update([
            'status' => $offer->status === 'active' ? 'inactive' : 'active',
            'updated_by' => $request->user()->id,
        ]);

        $activityLogger->log(
            'partner.web.discount_offer.status_toggled',
            'Changement de statut d une offre partenaire.',
            $offer,
            [
                'organization_id' => $organization->id,
                'code' => $offer->code,
                'status' => $offer->status,
            ],
            $request,
            $request->user(),
            'partner',
        );

        return back()->with('success', 'Le statut de l offre a ete mis a jour.');
    }

    private function validateRequest(Request $request, ?PartnerDiscountOffer $offer = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('partner_discount_offers', 'code')->ignore($offer?->id)],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed_amount', 'custom'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'minimum_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses_per_card' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_day' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => [$offer ? 'required' : 'nullable', Rule::in(['draft', 'active', 'inactive', 'archived'])],
        ]);
    }
}
