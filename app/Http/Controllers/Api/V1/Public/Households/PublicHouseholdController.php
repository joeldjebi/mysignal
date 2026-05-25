<?php

namespace App\Http\Controllers\Api\V1\Public\Households;

use App\Domain\Households\Actions\AcceptHouseholdInvitationAction;
use App\Domain\Households\Actions\CreateHouseholdAction;
use App\Domain\Households\Actions\DeclineHouseholdInvitationAction;
use App\Domain\Households\Actions\DeleteHouseholdAction;
use App\Domain\Households\Actions\InviteHouseholdMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Households\AcceptHouseholdInvitationRequest;
use App\Http\Requests\Api\V1\Public\Households\DeclineHouseholdInvitationRequest;
use App\Http\Requests\Api\V1\Public\Households\InviteHouseholdMemberRequest;
use App\Http\Requests\Api\V1\Public\Households\StoreHouseholdRequest;
use App\Http\Resources\Api\V1\Public\Households\HouseholdInvitationResource;
use App\Http\Resources\Api\V1\Public\Households\HouseholdResource;
use App\Models\Household;
use App\Models\PublicUser;
use App\Services\Notifications\PushNotificationDispatcher;
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
        $households = $this->userHouseholds($request->user('public_api'));

        if ($households->isEmpty()) {
            return ApiResponse::success([
                'household' => null,
                'households' => [],
            ], 'Aucun Gonhi rattache a ce compte.');
        }

        return ApiResponse::success([
            'household' => new HouseholdResource($households->first()),
            'households' => HouseholdResource::collection($households),
        ]);
    }

    public function destroy(Request $request, Household $household, DeleteHouseholdAction $action)
    {
        $user = $request->user('public_api');

        $action->handle($user, $household);

        $households = $this->userHouseholds($user);

        return ApiResponse::success([
            'household' => $households->isNotEmpty() ? new HouseholdResource($households->first()) : null,
            'households' => HouseholdResource::collection($households),
        ], 'Gbonhi supprime avec succes.');
    }

    private function userHouseholds(PublicUser $user)
    {
        $members = $user
            ->householdMembers()
            ->with([
                'household.members.publicUser',
                'household.invitations' => fn ($query) => $query->whereNull('accepted_at'),
            ])
            ->latest('id')
            ->get();

        return $members
            ->map(fn ($member) => $member->household)
            ->filter()
            ->values();
    }

    public function invite(Request $request, Household $household, InviteHouseholdMemberRequest $inviteRequest, InviteHouseholdMemberAction $action, PushNotificationDispatcher $notifications)
    {
        $invitation = $action->handle($request->user('public_api'), $household, $inviteRequest->validated());
        $invitedUser = PublicUser::query()->where('phone', $invitation->phone)->first();

        if ($invitedUser instanceof PublicUser) {
            $notifications->notifyPublicUser(
                $invitedUser,
                'household_invitation_created',
                'Invitation Gbonhi reçue',
                'Vous avez reçu une invitation à rejoindre un Gbonhi.',
                [
                    'screen' => 'household',
                    'invitation_id' => $invitation->id,
                    'household_id' => $household->id,
                ],
            );
        }

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
            ->whereNull('declined_at');
    }
}
