<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user !== null && $user->organization_id !== null, 403);

        $query = ActivityLog::query()
            ->with('subject')
            ->where('actor_user_id', $user->id)
            ->latest();

        if (filled(request('date_from'))) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (filled(request('date_to'))) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        return view('institution.activity-logs.index', [
            'activeNav' => 'activity-logs',
            'logs' => $query->paginate(20)->withQueryString(),
        ]);
    }
}
