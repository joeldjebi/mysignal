<?php

namespace App\Http\Controllers\Api\V1\Public\Households;

use App\Domain\Households\Actions\AcceptHouseholdInvitationAction;
use App\Domain\Households\Actions\CancelHouseholdInvitationAction;
use App\Domain\Households\Actions\CreateHouseholdAction;
use App\Domain\Households\Actions\DeclineHouseholdInvitationAction;
use App\Domain\Households\Actions\DeleteHouseholdAction;
use App\Domain\Households\Actions\InviteHouseholdMemberAction;
use App\Domain\Households\Actions\RemoveHouseholdMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Households\AcceptHouseholdInvitationRequest;
use App\Http\Requests\Api\V1\Public\Households\DeclineHouseholdInvitationRequest;
use App\Http\Requests\Api\V1\Public\Households\InviteHouseholdMemberRequest;
use App\Http\Requests\Api\V1\Public\Households\StoreHouseholdRequest;
use App\Http\Resources\Api\V1\Public\Households\HouseholdInvitationResource;
use App\Http\Resources\Api\V1\Public\Households\HouseholdResource;
use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\HouseholdMember;
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
            'invitations' => fn ($query) => $query->whereNull('accepted_at')->whereNull('declined_at'),
        ]);

        return ApiResponse::success([
            'household' => new HouseholdResource($household),
        ], 'Gbonhi créé avec succès.', 201);
    }

    public function showMine(Request $request)
    {
        $households = $this->userHouseholds($request->user('public_api'));

        if ($households->isEmpty()) {
            return ApiResponse::success([
                'household' => null,
                'households' => [],
            ], 'Aucun Gbonhi rattaché à ce compte.');
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
        ], 'Gbonhi supprimé avec succès.');
    }

    private function userHouseholds(PublicUser $user)
    {
        $members = $user
            ->householdMembers()
            ->with([
                'household.members.publicUser',
                'household.invitations' => fn ($query) => $query->whereNull('accepted_at')->whereNull('declined_at'),
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
        ], 'Invitation Gbonhi envoyée avec succès.', 201);
    }

    public function cancelInvitation(Request $request, HouseholdInvitation $invitation, CancelHouseholdInvitationAction $action)
    {
        $action->handle($request->user('public_api'), $invitation);

        $households = $this->userHouseholds($request->user('public_api'));
        $selectedHousehold = $households
            ->first(fn ($household): bool => (int) $household->id === (int) $invitation->household_id)
            ?: $households->first();

        return ApiResponse::success([
            'invitation' => new HouseholdInvitationResource($invitation->fresh()),
            'household' => $selectedHousehold ? new HouseholdResource($selectedHousehold) : null,
            'households' => HouseholdResource::collection($households),
        ], 'Invitation Gbonhi annulée avec succès.');
    }

    public function removeMember(Request $request, Household $household, HouseholdMember $member, RemoveHouseholdMemberAction $action)
    {
        $user = $request->user('public_api');

        $action->handle($user, $household, $member);

        $households = $this->userHouseholds($user);
        $selectedHousehold = $households
            ->first(fn ($item): bool => (int) $item->id === (int) $household->id)
            ?: $households->first();

        return ApiResponse::success([
            'household' => $selectedHousehold ? new HouseholdResource($selectedHousehold) : null,
            'households' => HouseholdResource::collection($households),
        ], 'Membre retiré du Gbonhi avec succès.');
    }

    public function accept(AcceptHouseholdInvitationRequest $request, AcceptHouseholdInvitationAction $action)
    {
        $invitation = $action->handle($request->user('public_api'), $request->validated());

        $household = $invitation->household()
            ->with([
                'members.publicUser',
                'invitations' => fn ($query) => $query->whereNull('accepted_at')->whereNull('declined_at'),
            ])
            ->firstOrFail();

        return ApiResponse::success([
            'household' => new HouseholdResource($household),
        ], 'Invitation Gbonhi acceptée avec succès.');
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
        ], 'Invitation Gbonhi refusée avec succès.');
    }

    private function pendingInvitationsQuery(?string $phone)
    {
        return \App\Models\HouseholdInvitation::query()
            ->when($phone, fn ($query) => $query->where('phone', $phone))
            ->whereNull('accepted_at')
            ->whereNull('declined_at');
    }
}
