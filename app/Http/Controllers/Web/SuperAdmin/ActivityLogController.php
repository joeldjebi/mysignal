<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $actor = auth()->user()?->loadMissing('activityLogVisibleUsers');
        abort_unless($actor instanceof User, 403);

        $canViewAnyLogs = $actor->is_super_admin
            || $actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_SELF')
            || $actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_INSTITUTION')
            || $actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_PUBLIC')
            || $actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_INTERNAL');

        abort_unless($canViewAnyLogs, 403);

        $query = ActivityLog::query()
            ->with(['actorUser', 'actorPublicUser', 'subject'])
            ->latest();

        if (! $actor->is_super_admin) {
            $query->where(function (Builder $builder) use ($actor): void {
                if ($actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_SELF')) {
                    $builder->orWhere('actor_user_id', $actor->id);
                }

                if ($actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_INSTITUTION')) {
                    $builder->orWhere('portal', 'institution');
                }

                if ($actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_PUBLIC')) {
                    $builder->orWhere('actor_public_user_id', '!=', null);
                }

                if ($actor->hasPermissionCode('SA_ACTIVITY_LOGS_VIEW_INTERNAL')) {
                    $allowedInternalIds = $actor->activityLogVisibleUsers()
                        ->pluck('users.id')
                        ->push($actor->id)
                        ->unique()
                        ->values()
                        ->all();

                    if ($allowedInternalIds !== []) {
                        $builder->orWhereIn('actor_user_id', $allowedInternalIds);
                    }
                }
            });
        }

        if (filled(request('search'))) {
            $search = trim((string) request('search'));

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('action', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('portal', 'like', '%'.$search.'%')
                    ->orWhereHas('actorUser', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('actorPublicUser', function (Builder $publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled(request('portal'))) {
            $query->where('portal', request('portal'));
        }

        if (filled(request('action_type'))) {
            $query->where('action', request('action_type'));
        }

        if (filled(request('date_from'))) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (filled(request('date_to'))) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        return view('super-admin.activity-logs.index', [
            'logs' => $query->paginate(20)->withQueryString(),
            'portals' => ActivityLog::query()->select('portal')->distinct()->orderBy('portal')->pluck('portal'),
            'actions' => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
