<?php

namespace Tests\Feature\Public\Notifications;

use App\Models\PublicUser;
use App\Models\DeviceToken;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_register_device_token_and_read_notifications(): void
    {
        $user = PublicUser::query()->create([
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000400',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = $this->postJson('/api/v1/public/auth/login', [
            'phone' => '0700000400',
            'password' => 'secret123',
        ])->json('data.access_token');

        $this->withToken($token)->postJson('/api/v1/public/push-tokens', [
            'token' => 'fake-fcm-token',
            'platform' => 'android',
            'device_name' => 'Android test',
            'app_version' => '1.0.0',
        ])->assertOk();

        $this->assertDatabaseHas('device_tokens', [
            'recipient_type' => 'public',
            'recipient_id' => $user->id,
            'guard' => 'public_api',
            'platform' => 'android',
            'revoked_at' => null,
        ]);

        $notification = UserNotification::query()->create([
            'recipient_type' => 'public',
            'recipient_id' => $user->id,
            'type' => 'report_resolved',
            'title' => 'Signalement résolu',
            'body' => 'Votre signalement a été traité.',
            'data' => ['screen' => 'report_details', 'report_id' => 15],
        ]);

        $this->withToken($token)->getJson('/api/v1/public/notifications')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1)
            ->assertJsonPath('data.notifications.0.id', $notification->id)
            ->assertJsonPath('data.notifications.0.data.screen', 'report_details');

        $this->withToken($token)->postJson("/api/v1/public/notifications/{$notification->id}/read")
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);

        $this->withToken($token)->deleteJson('/api/v1/public/push-tokens', [
            'token' => 'fake-fcm-token',
        ])->assertOk();

        $this->assertNotNull(DeviceToken::query()
            ->where('token_hash', DeviceToken::hashToken('fake-fcm-token'))
            ->value('revoked_at'));
    }
}
