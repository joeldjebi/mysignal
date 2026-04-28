<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Notifications\UserNotificationResource;
use App\Models\UserNotification;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserNotificationController extends Controller
{
    public function publicIndex(Request $request)
    {
        return $this->index($request, 'public', 'public_api', true);
    }

    public function partnerIndex(Request $request)
    {
        return $this->index($request, 'partner', 'partner_api', false);
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

    private function index(Request $request, string $recipientType, string $guard, bool $withFilters)
    {
        $rules = [
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];

        if ($withFilters) {
            $rules += [
                'status' => ['nullable', Rule::in(['read', 'unread', 'all'])],
                'category' => ['nullable', 'string', 'max:80'],
                'search' => ['nullable', 'string', 'max:120'],
            ];
        }

        $attributes = $request->validate($rules);

        $user = $request->user($guard);
        $baseQuery = UserNotification::query()
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $user->id);

        $query = clone $baseQuery;

        if ($withFilters) {
            $query
                ->when(($attributes['status'] ?? null) === 'read', fn ($query) => $query->whereNotNull('read_at'))
                ->when(($attributes['status'] ?? null) === 'unread', fn ($query) => $query->whereNull('read_at'))
                ->when($attributes['search'] ?? null, function ($query, string $search): void {
                    $like = '%'.mb_strtolower($search).'%';
                    $query->where(function ($query) use ($like): void {
                        $query
                            ->whereRaw('LOWER(title) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(body) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(type) LIKE ?', [$like]);
                    });
                })
                ->when($attributes['category'] ?? null, fn ($query, string $category) => $this->applyCategoryFilter($query, $category));
        }

        $notifications = $query
            ->latest('id')
            ->limit((int) ($attributes['limit'] ?? 30))
            ->get();

        $data = [
            'notifications' => UserNotificationResource::collection($notifications),
            'unread_count' => (clone $baseQuery)
                ->whereNull('read_at')
                ->count(),
        ];

        if ($withFilters) {
            $data += [
                'filtered_count' => (clone $query)->count(),
                'available_categories' => $this->availableCategories((clone $baseQuery)->get(['type', 'data'])),
                'filters' => [
                    'status' => $attributes['status'] ?? 'all',
                    'category' => $attributes['category'] ?? null,
                    'search' => $attributes['search'] ?? null,
                    'limit' => (int) ($attributes['limit'] ?? 30),
                ],
            ];
        }

        return ApiResponse::success($data);
    }

    private function applyCategoryFilter($query, string $category): void
    {
        $mappedTypes = match ($category) {
            'mysignal', 'super_admin' => ['super_admin_broadcast'],
            'gbonhi', 'household' => ['household_invitation_created'],
            'discount', 'discounts', 'partner_discount' => ['partner_discount_applied', 'public_discount_received'],
            default => [],
        };

        $query->where(function ($query) use ($category, $mappedTypes): void {
            foreach ($mappedTypes as $type) {
                $query->orWhere('type', $type);
            }

            if ($category === 'general') {
                $query
                    ->orWhere(function ($query): void {
                        $query
                            ->whereNotIn('type', ['super_admin_broadcast', 'household_invitation_created', 'partner_discount_applied', 'public_discount_received'])
                            ->whereNull('data->category')
                            ->whereNull('data->source');
                    });
            }

            $query
                ->orWhere('type', $category)
                ->orWhere('data->category', $category)
                ->orWhere('data->source', $category);
        });
    }

    private function availableCategories($notifications): array
    {
        return $notifications
            ->map(fn (UserNotification $notification): array => [
                'key' => $this->categoryKey($notification),
                'label' => $this->categoryLabel($notification),
            ])
            ->unique('key')
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function categoryKey(UserNotification $notification): string
    {
        return match ($notification->type) {
            'super_admin_broadcast' => 'mysignal',
            'household_invitation_created' => 'gbonhi',
            'partner_discount_applied', 'public_discount_received' => 'discount',
            default => (string) data_get($notification->data, 'category', data_get($notification->data, 'source', 'general')),
        };
    }

    private function categoryLabel(UserNotification $notification): string
    {
        return match ($this->categoryKey($notification)) {
            'mysignal', 'super_admin' => 'Information MYSIGNAL',
            'gbonhi', 'household' => 'Gbonhi',
            'report', 'reports' => 'Signalement',
            'payment', 'payments' => 'Paiement',
            'subscription', 'subscriptions' => 'Abonnement',
            'discount', 'discounts', 'partner_discount' => 'Remise',
            default => 'Général',
        };
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
