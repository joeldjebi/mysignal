<?php

namespace App\Http\Controllers\Api\V1\Partner\Discounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Partner\Discounts\StorePartnerDiscountOfferRequest;
use App\Http\Requests\Api\V1\Partner\Discounts\UpdatePartnerDiscountOfferRequest;
use App\Http\Resources\Api\V1\Partner\Discounts\PartnerDiscountOfferResource;
use App\Models\PartnerDiscountOffer;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PartnerDiscountOfferController extends Controller
{
    public function index(Request $request)
    {
        $offers = PartnerDiscountOffer::query()
            ->where('organization_id', $request->user('partner_api')->organization_id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->orderBy('name')
            ->get();

        return ApiResponse::success([
            'offers' => PartnerDiscountOfferResource::collection($offers),
        ]);
    }

    public function store(StorePartnerDiscountOfferRequest $request, ActivityLogger $activityLogger)
    {
        $offer = PartnerDiscountOffer::query()->create([
            'organization_id' => $request->user('partner_api')->organization_id,
            'code' => $request->string('code')->value(),
            'name' => $request->string('name')->value(),
            'description' => $request->input('description'),
            'discount_type' => $request->string('discount_type')->value(),
            'discount_value' => $request->input('discount_value'),
            'currency' => $request->input('currency'),
            'minimum_purchase_amount' => $request->input('minimum_purchase_amount'),
            'maximum_discount_amount' => $request->input('maximum_discount_amount'),
            'max_uses_per_card' => $request->input('max_uses_per_card'),
            'max_uses_per_day' => $request->input('max_uses_per_day'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'status' => $request->input('status', 'draft'),
            'metadata' => $request->input('metadata'),
            'created_by' => $request->user('partner_api')->id,
            'updated_by' => $request->user('partner_api')->id,
        ]);

        $activityLogger->log(
            'partner.discount_offer.created',
            'Creation d une offre de reduction partenaire.',
            $offer,
            [
                'organization_id' => $offer->organization_id,
                'code' => $offer->code,
                'status' => $offer->status,
            ],
            $request,
            $request->user('partner_api'),
            'partner',
        );

        return ApiResponse::success([
            'offer' => new PartnerDiscountOfferResource($offer),
        ], 'Offre partenaire creee avec succes.', 201);
    }

    public function update(UpdatePartnerDiscountOfferRequest $request, PartnerDiscountOffer $offer, ActivityLogger $activityLogger)
    {
        $this->assertSameOrganization($request, $offer);

        $offer->update([
            'code' => $request->string('code')->value(),
            'name' => $request->string('name')->value(),
            'description' => $request->input('description'),
            'discount_type' => $request->string('discount_type')->value(),
            'discount_value' => $request->input('discount_value'),
            'currency' => $request->input('currency'),
            'minimum_purchase_amount' => $request->input('minimum_purchase_amount'),
            'maximum_discount_amount' => $request->input('maximum_discount_amount'),
            'max_uses_per_card' => $request->input('max_uses_per_card'),
            'max_uses_per_day' => $request->input('max_uses_per_day'),
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'status' => $request->string('status')->value(),
            'metadata' => $request->input('metadata'),
            'updated_by' => $request->user('partner_api')->id,
        ]);

        $activityLogger->log(
            'partner.discount_offer.updated',
            'Mise a jour d une offre de reduction partenaire.',
            $offer,
            [
                'organization_id' => $offer->organization_id,
                'code' => $offer->code,
                'status' => $offer->status,
            ],
            $request,
            $request->user('partner_api'),
            'partner',
        );

        return ApiResponse::success([
            'offer' => new PartnerDiscountOfferResource($offer->fresh()),
        ], 'Offre partenaire mise a jour avec succes.');
    }

    public function toggleStatus(Request $request, PartnerDiscountOffer $offer, ActivityLogger $activityLogger)
    {
        $this->assertSameOrganization($request, $offer);

        $nextStatus = match ($offer->status) {
            'active' => 'inactive',
            'inactive', 'draft' => 'active',
            default => 'inactive',
        };

        $offer->update([
            'status' => $nextStatus,
            'updated_by' => $request->user('partner_api')->id,
        ]);

        $activityLogger->log(
            'partner.discount_offer.status_toggled',
            'Changement de statut d une offre partenaire.',
            $offer,
            [
                'organization_id' => $offer->organization_id,
                'code' => $offer->code,
                'status' => $offer->status,
            ],
            $request,
            $request->user('partner_api'),
            'partner',
        );

        return ApiResponse::success([
            'offer' => new PartnerDiscountOfferResource($offer->fresh()),
        ], 'Statut de l offre partenaire mis a jour.');
    }

    private function assertSameOrganization(Request $request, PartnerDiscountOffer $offer): void
    {
        if ((int) $offer->organization_id !== (int) $request->user('partner_api')->organization_id) {
            throw ValidationException::withMessages([
                'offer' => ['Cette offre n appartient pas a votre etablissement partenaire.'],
            ]);
        }
    }
}
