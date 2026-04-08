<?php

namespace App\Http\Controllers\Api\V1\Public\Households;

use App\Domain\Households\Actions\AcceptHouseholdInvitationAction;
use App\Domain\Households\Actions\CreateHouseholdAction;
use App\Domain\Households\Actions\DeclineHouseholdInvitationAction;
use App\Domain\Households\Actions\InviteHouseholdMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Households\AcceptHouseholdInvitationRequest;
use App\Http\Requests\Api\V1\Public\Households\DeclineHouseholdInvitationRequest;
use App\Http\Requests\Api\V1\Public\Households\InviteHouseholdMemberRequest;
use App\Http\Requests\Api\V1\Public\Households\StoreHouseholdRequest;
use App\Http\Resources\Api\V1\Public\Households\HouseholdInvitationResource;
use App\Http\Resources\Api\V1\Public\Households\HouseholdResource;
use App\Models\Household;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class PublicHouseholdController extends Controller
{
    public function pendingInvitations(Request $request)
    {
        $invitations = $this->pendingInvitationsQuery($request->user('public_api')->phone)
            ->with(['household', 'meter'])
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'invitations' => HouseholdInvitationResource::collection($invitations),
        ]);
    }

    public function store(StoreHouseholdRequest $request, CreateHouseholdAction $action)
    {
        $household = $action->handle($request->user('public_api'), $request->validated());

        $household->load([
            'members.publicUser',
            'invitations' => fn ($query) => $query->whereNull('accepted_at'),
        ]);

        return ApiResponse::success([
            'household' => new HouseholdResource($household),
        ], 'Gonhi cree avec succes.', 201);
    }

    public function showMine(Request $request)
    {
        $member = $request->user('public_api')
            ->householdMembers()
            ->with([
                'household.members.publicUser',
                'household.invitations' => fn ($query) => $query->whereNull('accepted_at'),
            ])
            ->latest('id')
            ->first();

        if ($member === null) {
            return ApiResponse::success([
                'household' => null,
            ], 'Aucun Gonhi rattache a ce compte.');
        }

        return ApiResponse::success([
            'household' => new HouseholdResource($member->household),
        ]);
    }

    public function invite(Request $request, Household $household, InviteHouseholdMemberRequest $inviteRequest, InviteHouseholdMemberAction $action)
    {
        $invitation = $action->handle($request->user('public_api'), $household, $inviteRequest->validated());

        return ApiResponse::success([
            'invitation' => new HouseholdInvitationResource($invitation),
        ], 'Invitation Gonhi envoyee avec succes.', 201);
    }

    public function accept(AcceptHouseholdInvitationRequest $request, AcceptHouseholdInvitationAction $action)
    {
        $invitation = $action->handle($request->user('public_api'), $request->validated());

        $household = $invitation->household()
            ->with([
                'members.publicUser',
                'invitations' => fn ($query) => $query->whereNull('accepted_at'),
            ])
            ->firstOrFail();

        return ApiResponse::success([
            'household' => new HouseholdResource($household),
        ], 'Invitation Gonhi acceptee avec succes.');
    }

    public function decline(DeclineHouseholdInvitationRequest $request, DeclineHouseholdInvitationAction $action)
    {
        $action->handle($request->user('public_api'), $request->validated());

        return ApiResponse::success([
            'invitations' => HouseholdInvitationResource::collection(
                $this->pendingInvitationsQuery($request->user('public_api')->phone)
                    ->with(['household', 'meter'])
                    ->latest('id')
                    ->get()
            ),
        ], 'Invitation Gonhi declinee avec succes.');
    }

    private function pendingInvitationsQuery(?string $phone)
    {
        return \App\Models\HouseholdInvitation::query()
            ->when($phone, fn ($query) => $query->where('phone', $phone))
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->where('expires_at', '>', now());
    }
}
