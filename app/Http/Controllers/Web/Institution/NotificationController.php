<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(Request $request): View
    {
        $context = $this->institutionContext();
        $status = $request->query('status', 'all');
        $category = $request->query('category');
        $search = trim((string) $request->query('search', ''));
        $user = $request->user();

        $baseQuery = UserNotification::query()
            ->where('recipient_type', 'institution')
            ->where('recipient_id', $user->id);

        $query = (clone $baseQuery)
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when(filled($category), fn ($query) => $this->applyCategoryFilter($query, (string) $category))
            ->when(filled($search), function ($query) use ($search): void {
                $like = '%'.mb_strtolower($search).'%';
                $query->where(function ($query) use ($like): void {
                    $query
                        ->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(body) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(type) LIKE ?', [$like]);
                });
            });

        $notifications = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $availableCategories = $this->availableCategories((clone $baseQuery)->get(['type', 'data']));

        return view('institution.notifications.index', [
            ...$context,
            'features' => $context['feature_codes'],
            'activeNav' => 'notifications',
            'notifications' => $notifications,
            'unreadCount' => (clone $baseQuery)->whereNull('read_at')->count(),
            'totalCount' => (clone $baseQuery)->count(),
            'availableCategories' => $availableCategories,
            'filters' => [
                'status' => in_array($status, ['all', 'read', 'unread'], true) ? $status : 'all',
                'category' => $category,
                'search' => $search,
            ],
        ]);
    }

    public function markAsRead(Request $request, UserNotification $notification): RedirectResponse
    {
        $this->abortIfNotOwned($request, $notification);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return back()->with('success', 'Notification marquee comme lue.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        UserNotification::query()
            ->where('recipient_type', 'institution')
            ->where('recipient_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications ont ete marquees comme lues.');
    }

    private function abortIfNotOwned(Request $request, UserNotification $notification): void
    {
        abort_if(
            $notification->recipient_type !== 'institution'
            || (int) $notification->recipient_id !== (int) $request->user()->id,
            404
        );
    }

    private function applyCategoryFilter($query, string $category): void
    {
        $mappedTypes = match ($category) {
            'report' => ['institution_report_created', 'institution_damage_declared'],
            'damage' => ['institution_damage_declared'],
            'reparation_case' => [
                'institution_reparation_case_opened',
                'institution_reparation_case_updated',
                'institution_reparation_case_step_added',
            ],
            default => [],
        };

        $query->where(function ($query) use ($category, $mappedTypes): void {
            foreach ($mappedTypes as $type) {
                $query->orWhere('type', $type);
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
            'institution_report_created' => 'report',
            'institution_damage_declared' => 'damage',
            'institution_reparation_case_opened',
            'institution_reparation_case_updated',
            'institution_reparation_case_step_added' => 'reparation_case',
            default => (string) data_get($notification->data, 'category', data_get($notification->data, 'source', 'general')),
        };
    }

    private function categoryLabel(UserNotification $notification): string
    {
        return match ($this->categoryKey($notification)) {
            'report' => 'Signalements',
            'damage' => 'Dommages',
            'reparation_case' => 'Dossiers',
            'public_user' => 'Usagers publics',
            'super_admin' => 'Backoffice',
            default => 'General',
        };
    }
}
