<?php

namespace App\Services\Notifications;

use App\Models\DeviceToken;
use App\Models\PublicUser;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class PushNotificationDispatcher
{
    public function __construct(private readonly FirebaseCloudMessagingClient $firebase)
    {
    }

    public function notifyPublicUser(PublicUser $user, string $type, string $title, ?string $body = null, array $data = []): UserNotification
    {
        return $this->notify('public', $user->id, $type, $title, $body, $data);
    }

    public function notifyPartnerUser(User $user, string $type, string $title, ?string $body = null, array $data = []): UserNotification
    {
        return $this->notify('partner', $user->id, $type, $title, $body, $data);
    }

    private function notify(string $recipientType, int $recipientId, string $type, string $title, ?string $body, array $data): UserNotification
    {
        $notification = UserNotification::query()->create([
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        $tokens = DeviceToken::query()
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->whereNull('revoked_at')
            ->pluck('token')
            ->all();

        try {
            $this->firebase->sendToTokens($tokens, $title, $body, [
                ...$data,
                'type' => $type,
                'notification_id' => $notification->id,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Unable to send Firebase push notification.', [
                'notification_id' => $notification->id,
                'recipient_type' => $recipientType,
                'recipient_id' => $recipientId,
                'error' => $exception->getMessage(),
            ]);
        }

        return $notification;
    }
}
