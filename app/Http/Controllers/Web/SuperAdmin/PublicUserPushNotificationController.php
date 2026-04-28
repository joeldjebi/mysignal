<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PublicUser;
use App\Models\SuperAdminPushNotification;
use App\Services\Notifications\PushNotificationDispatcher;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicUserPushNotificationController extends Controller
{
    public function index(): View
    {
        $eligibleUsers = PublicUser::query()
            ->whereHas('activeDeviceTokens')
            ->with(['publicUserType', 'activeDeviceTokens', 'latestDeviceToken'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('super-admin.public-users.push-notifications', [
            'eligibleUsers' => $eligibleUsers,
            'eligibleUsersCount' => $eligibleUsers->count(),
            'history' => SuperAdminPushNotification::query()
                ->with('sender')
                ->latest('sent_at')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function store(Request $request, PushNotificationDispatcher $notifications, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'target_scope' => ['required', Rule::in(['selected', 'all'])],
            'public_user_ids' => ['nullable', 'array'],
            'public_user_ids.*' => ['integer', 'exists:public_users,id'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $query = PublicUser::query()->whereHas('activeDeviceTokens');
        $selectedIds = collect($attributes['public_user_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($attributes['target_scope'] === 'selected') {
            if ($selectedIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'public_user_ids' => 'Selectionnez au moins un UP avec un token notification actif.',
                ]);
            }

            $query->whereIn('id', $selectedIds);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            throw ValidationException::withMessages([
                'public_user_ids' => 'Aucun UP eligible avec token notification actif.',
            ]);
        }

        $campaign = SuperAdminPushNotification::query()->create([
            'sent_by_user_id' => $request->user()?->id,
            'target_scope' => $attributes['target_scope'],
            'status' => 'pending',
            'title' => $attributes['title'],
            'body' => $attributes['body'],
            'requested_count' => $users->count(),
            'target_user_ids' => $users->pluck('id')->values()->all(),
            'sent_at' => now(),
        ]);

        $sentUserIds = [];
        $failedUserIds = [];

        foreach ($users as $user) {
            try {
                $notifications->notifyPublicUser(
                    $user,
                    'super_admin_broadcast',
                    $attributes['title'],
                    $attributes['body'],
                    [
                        'screen' => 'dashboard',
                        'source' => 'super_admin',
                        'campaign_id' => $campaign->id,
                    ],
                );
                $sentUserIds[] = $user->id;
            } catch (\Throwable) {
                $failedUserIds[] = $user->id;
            }
        }

        $sentCount = count($sentUserIds);
        $failedCount = count($failedUserIds);
        $status = $sentCount === 0 ? 'failed' : ($failedCount > 0 ? 'partial' : 'sent');

        $campaign->update([
            'status' => $status,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'sent_user_ids' => $sentUserIds,
            'failed_user_ids' => $failedUserIds,
        ]);

        $activityLogger->log(
            'public_user.push_notification_sent',
            'Envoi d une notification push aux usagers publics.',
            $campaign,
            [
                'target_scope' => $attributes['target_scope'],
                'recipient_count' => $sentCount,
                'failed_count' => $failedCount,
                'title' => $attributes['title'],
            ],
            $request,
            $request->user(),
        );

        return redirect()
            ->route('super-admin.public-users.push-notifications.index')
            ->with('success', $sentCount.' notification(s) envoyee(s), '.$failedCount.' echec(s).');
    }
}
