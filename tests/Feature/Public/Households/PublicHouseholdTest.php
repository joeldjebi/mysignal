<?php

namespace Tests\Feature\Public\Households;

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
            ->assertJsonPath('data.household.name', 'Foyer Yao');
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
            ->assertJsonPath('data.invitation.phone', '0700000302');

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

    private function loginAndGetToken(string $phone, string $password): string
    {
        return $this->postJson('/api/v1/public/auth/login', [
            'phone' => $phone,
            'password' => $password,
        ])->json('data.access_token');
    }
}
