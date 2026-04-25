<?php

namespace Tests\Feature\Public\Households;

use App\Models\HouseholdInvitation;
use App\Models\PublicUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_household_and_view_it(): void
    {
        $owner = PublicUser::query()->create([
            'first_name' => 'Claire',
            'last_name' => 'Yao',
            'phone' => '0700000300',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = $this->loginAndGetToken('0700000300', 'secret123');

        $createResponse = $this->withToken($token)->postJson('/api/v1/public/households', [
            'name' => 'Foyer Yao',
            'address' => 'Riviera 2',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.household.name', 'Foyer Yao')
            ->assertJsonPath('data.household.members.0.user.phone', '0700000300');

        $this->withToken($token)
            ->getJson('/api/v1/public/households/me')
            ->assertOk()
            ->assertJsonPath('data.household.name', 'Foyer Yao')
            ->assertJsonCount(1, 'data.households');
    }

    public function test_owner_can_create_multiple_households(): void
    {
        PublicUser::query()->create([
            'first_name' => 'Claire',
            'last_name' => 'Yao',
            'phone' => '0700000305',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = $this->loginAndGetToken('0700000305', 'secret123');

        $this->withToken($token)->postJson('/api/v1/public/households', [
            'name' => 'Foyer Yao',
        ])->assertCreated();

        $this->withToken($token)->postJson('/api/v1/public/households', [
            'name' => 'Foyer Famille',
        ])->assertCreated();

        $this->withToken($token)
            ->getJson('/api/v1/public/households/me')
            ->assertOk()
            ->assertJsonCount(2, 'data.households')
            ->assertJsonPath('data.household.name', 'Foyer Famille');
    }

    public function test_owner_can_invite_and_member_can_accept_household_invitation(): void
    {
        $owner = PublicUser::query()->create([
            'first_name' => 'Claire',
            'last_name' => 'Yao',
            'phone' => '0700000301',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $member = PublicUser::query()->create([
            'first_name' => 'Kevin',
            'last_name' => 'Yao',
            'phone' => '0700000302',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $ownerToken = $this->loginAndGetToken('0700000301', 'secret123');

        $householdId = $this->withToken($ownerToken)->postJson('/api/v1/public/households', [
            'name' => 'Foyer Yao',
        ])->json('data.household.id');

        $inviteResponse = $this->withToken($ownerToken)->postJson("/api/v1/public/households/{$householdId}/invitations", [
            'phone' => '0700000302',
            'relationship' => 'child',
        ]);

        $inviteResponse->assertCreated()
            ->assertJsonPath('data.invitation.phone', '0700000302')
            ->assertJsonPath('data.invitation.expires_at', null)
            ->assertJsonPath('data.invitation.status', 'pending');

        $invitationId = $inviteResponse->json('data.invitation.id');
        $code = $inviteResponse->json('data.invitation.code_for_testing');
        $memberToken = $this->loginAndGetToken('0700000302', 'secret123');

        $acceptResponse = $this->withToken($memberToken)->postJson('/api/v1/public/households/invitations/accept', [
            'invitation_id' => $invitationId,
            'code' => $code,
        ]);

        $acceptResponse->assertOk()
            ->assertJsonCount(2, 'data.household.members');

        $this->assertDatabaseHas('household_members', [
            'public_user_id' => $member->id,
            'relationship' => 'child',
        ]);
    }

    public function test_pending_invitations_do_not_expire(): void
    {
        PublicUser::query()->create([
            'first_name' => 'Claire',
            'last_name' => 'Yao',
            'phone' => '0700000306',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $member = PublicUser::query()->create([
            'first_name' => 'Kevin',
            'last_name' => 'Yao',
            'phone' => '0700000307',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $ownerToken = $this->loginAndGetToken('0700000306', 'secret123');
        $memberToken = $this->loginAndGetToken('0700000307', 'secret123');

        $householdId = $this->withToken($ownerToken)->postJson('/api/v1/public/households', [
            'name' => 'Foyer Yao',
        ])->json('data.household.id');

        $inviteResponse = $this->withToken($ownerToken)->postJson("/api/v1/public/households/{$householdId}/invitations", [
            'phone' => '0700000307',
            'relationship' => 'child',
        ]);

        $invitationId = $inviteResponse->json('data.invitation.id');

        HouseholdInvitation::query()
            ->whereKey($invitationId)
            ->update(['expires_at' => now()->subDay()]);

        $this->withToken($memberToken)
            ->getJson('/api/v1/public/households/invitations/pending')
            ->assertOk()
            ->assertJsonCount(1, 'data.invitations')
            ->assertJsonPath('data.invitations.0.status', 'pending');

        $this->withToken($memberToken)->postJson('/api/v1/public/households/invitations/accept', [
            'invitation_id' => $invitationId,
        ])->assertOk();

        $this->assertDatabaseHas('household_members', [
            'public_user_id' => $member->id,
            'household_id' => $householdId,
        ]);
    }

    public function test_member_can_accept_invitations_from_multiple_households(): void
    {
        $firstOwner = PublicUser::query()->create([
            'first_name' => 'Claire',
            'last_name' => 'Yao',
            'phone' => '0700000310',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $secondOwner = PublicUser::query()->create([
            'first_name' => 'Serge',
            'last_name' => 'Kouassi',
            'phone' => '0700000311',
            'commune' => 'Yopougon',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $member = PublicUser::query()->create([
            'first_name' => 'Kevin',
            'last_name' => 'Yao',
            'phone' => '0700000312',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $firstOwnerToken = $this->loginAndGetToken('0700000310', 'secret123');
        $secondOwnerToken = $this->loginAndGetToken('0700000311', 'secret123');
        $memberToken = $this->loginAndGetToken('0700000312', 'secret123');

        $firstHouseholdId = $this->withToken($firstOwnerToken)->postJson('/api/v1/public/households', [
            'name' => 'Foyer Yao',
        ])->json('data.household.id');

        $secondHouseholdId = $this->withToken($secondOwnerToken)->postJson('/api/v1/public/households', [
            'name' => 'Foyer Kouassi',
        ])->json('data.household.id');

        $firstInvite = $this->withToken($firstOwnerToken)->postJson("/api/v1/public/households/{$firstHouseholdId}/invitations", [
            'phone' => '0700000312',
            'relationship' => 'child',
        ]);

        $secondInvite = $this->withToken($secondOwnerToken)->postJson("/api/v1/public/households/{$secondHouseholdId}/invitations", [
            'phone' => '0700000312',
            'relationship' => 'brother',
        ]);

        $this->withToken($memberToken)->postJson('/api/v1/public/households/invitations/accept', [
            'invitation_id' => $firstInvite->json('data.invitation.id'),
            'code' => $firstInvite->json('data.invitation.code_for_testing'),
        ])->assertOk();

        $this->withToken($memberToken)->postJson('/api/v1/public/households/invitations/accept', [
            'invitation_id' => $secondInvite->json('data.invitation.id'),
            'code' => $secondInvite->json('data.invitation.code_for_testing'),
        ])->assertOk();

        $this->withToken($memberToken)
            ->getJson('/api/v1/public/households/me')
            ->assertOk()
            ->assertJsonCount(2, 'data.households');

        $this->assertDatabaseHas('household_members', [
            'public_user_id' => $member->id,
            'household_id' => $firstHouseholdId,
        ]);

        $this->assertDatabaseHas('household_members', [
            'public_user_id' => $member->id,
            'household_id' => $secondHouseholdId,
        ]);

        $this->assertDatabaseHas('household_members', [
            'public_user_id' => $firstOwner->id,
            'household_id' => $firstHouseholdId,
            'is_owner' => true,
        ]);

        $this->assertDatabaseHas('household_members', [
            'public_user_id' => $secondOwner->id,
            'household_id' => $secondHouseholdId,
            'is_owner' => true,
        ]);
    }

    private function loginAndGetToken(string $phone, string $password): string
    {
        return $this->postJson('/api/v1/public/auth/login', [
            'phone' => $phone,
            'password' => $password,
        ])->json('data.access_token');
    }
}
