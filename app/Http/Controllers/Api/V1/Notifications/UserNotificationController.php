<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Notifications\UserNotificationResource;
use App\Models\UserNotification;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function publicIndex(Request $request)
    {
        return $this->index($request, 'public', 'public_api');
    }

    public function partnerIndex(Request $request)
    {
        return $this->index($request, 'partner', 'partner_api');
    }

    public function publicMarkAsRead(Request $request, UserNotification $notification)
    {
        return $this->markAsRead($request, $notification, 'public', 'public_api');
    }

    public function partnerMarkAsRead(Request $request, UserNotification $notification)
    {
        return $this->markAsRead($request, $notification, 'partner', 'partner_api');
    }

    public function publicMarkAllAsRead(Request $request)
    {
        return $this->markAllAsRead($request, 'public', 'public_api');
    }

    public function partnerMarkAllAsRead(Request $request)
    {
        return $this->markAllAsRead($request, 'partner', 'partner_api');
    }

    private function index(Request $request, string $recipientType, string $guard)
    {
        $user = $request->user($guard);
        $notifications = UserNotification::query()
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $user->id)
            ->latest('id')
            ->limit((int) min(max($request->integer('limit', 30), 1), 100))
            ->get();

        return ApiResponse::success([
            'notifications' => UserNotificationResource::collection($notifications),
            'unread_count' => UserNotification::query()
                ->where('recipient_type', $recipientType)
                ->where('recipient_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    private function markAsRead(Request $request, UserNotification $notification, string $recipientType, string $guard)
    {
        $this->abortIfNotOwned($request, $notification, $recipientType, $guard);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return ApiResponse::success([
            'notification' => new UserNotificationResource($notification),
        ], 'Notification marquee comme lue.');
    }

    private function markAllAsRead(Request $request, string $recipientType, string $guard)
    {
        UserNotification::query()
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $request->user($guard)->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ApiResponse::success([], 'Notifications marquees comme lues.');
    }

    private function abortIfNotOwned(Request $request, UserNotification $notification, string $recipientType, string $guard): void
    {
        abort_if(
            $notification->recipient_type !== $recipientType
            || (int) $notification->recipient_id !== (int) $request->user($guard)->id,
            404
        );
    }
}
