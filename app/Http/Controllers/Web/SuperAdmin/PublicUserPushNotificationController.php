<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Models\City;
use App\Models\Commune;
use App\Models\Neighborhood;
use App\Models\OrganizationType;
use App\Models\PublicUser;
use App\Models\SignalType;
use App\Models\SubNeighborhood;
use App\Models\SuperAdminPushNotification;
use App\Services\Notifications\PushNotificationDispatcher;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
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
            'cities' => City::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'communes' => Commune::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'city_id']),
            'neighborhoods' => Neighborhood::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'commune_id']),
            'subNeighborhoods' => SubNeighborhood::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'neighborhood_id']),
            'signalTypes' => SignalType::query()->where('status', 'active')->orderBy('label')->get(['id', 'label', 'code']),
            'organizationTypes' => OrganizationType::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
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
            'target_scope' => ['required', Rule::in(['selected', 'all', 'filtered'])],
            'public_user_ids' => ['nullable', 'array'],
            'public_user_ids.*' => ['integer', 'exists:public_users,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,id'],
            'neighborhood_id' => ['nullable', 'integer', 'exists:neighborhoods,id'],
            'sub_neighborhood_id' => ['nullable', 'integer', 'exists:sub_neighborhoods,id'],
            'signal_type_id' => ['nullable', 'integer', 'exists:signal_types,id'],
            'organization_type_id' => ['nullable', 'integer', 'exists:organization_types,id'],
            'report_resolution_status' => ['nullable', Rule::in(['resolved', 'unresolved'])],
            'report_date_from' => ['nullable', 'date'],
            'report_date_to' => ['nullable', 'date', 'after_or_equal:report_date_from'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $query = PublicUser::query()->whereHas('activeDeviceTokens');
        $selectedIds = collect($attributes['public_user_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $targetFilters = $attributes['target_scope'] === 'filtered'
            ? $this->targetFilters($attributes)
            : [];

        if ($attributes['target_scope'] === 'selected') {
            if ($selectedIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'public_user_ids' => 'Sélectionnez au moins un UP avec les notifications actives.',
                ]);
            }

            $query->whereIn('id', $selectedIds);
        }

        if ($attributes['target_scope'] === 'filtered' && $targetFilters === []) {
            return back()
                ->withInput()
                ->withErrors(['target_scope' => 'Sélectionnez au moins un filtre ou utilisez l’envoi à tous les UP.']);
        }

        if ($attributes['target_scope'] === 'filtered') {
            $this->applyAudienceFilters($query, $attributes);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            throw ValidationException::withMessages([
                'public_user_ids' => 'Aucun UP éligible avec les notifications actives.',
            ]);
        }

        $campaignPayload = [
            'sent_by_user_id' => $request->user()?->id,
            'target_scope' => $attributes['target_scope'],
            'status' => 'pending',
            'title' => $attributes['title'],
            'body' => $attributes['body'],
            'requested_count' => $users->count(),
            'target_user_ids' => $users->pluck('id')->values()->all(),
            'sent_at' => now(),
        ];

        if (Schema::hasColumn('super_admin_push_notifications', 'target_filters')) {
            $campaignPayload['target_filters'] = $targetFilters;
        }

        $campaign = SuperAdminPushNotification::query()->create($campaignPayload);

        $sentUserIds = [];
        $failedUserIds = [];
        $failureDetails = [];

        foreach ($users as $user) {
            try {
                $result = $notifications->notifyPublicUserWithResult(
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

                if ((int) $result['sent'] > 0) {
                    $sentUserIds[] = $user->id;
                    continue;
                }

                $failedUserIds[] = $user->id;
                $failureDetails[] = [
                    'public_user_id' => $user->id,
                    'phone' => $user->phone,
                    'errors' => $result['errors'] ?? [],
                ];
            } catch (\Throwable) {
                $failedUserIds[] = $user->id;
                $failureDetails[] = [
                    'public_user_id' => $user->id,
                    'phone' => $user->phone,
                    'errors' => [['message' => 'Erreur interne pendant l’envoi.']],
                ];
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
            'failure_details' => $failureDetails,
        ]);

        $activityLogger->log(
            'public_user.push_notification_sent',
            'Envoi d’une notification aux usagers publics.',
            $campaign,
            [
                'target_scope' => $attributes['target_scope'],
                'target_filters' => $targetFilters,
                'recipient_count' => $sentCount,
                'failed_count' => $failedCount,
                'title' => $attributes['title'],
            ],
            $request,
            $request->user(),
        );

        return redirect()
            ->route('super-admin.public-users.push-notifications.index')
            ->with('success', $sentCount.' notification(s) envoyée(s), '.$failedCount.' échec(s).');
    }

    private function applyAudienceFilters($query, array $attributes): void
    {
        if (filled($attributes['city_id'] ?? null)) {
            $city = City::query()->find($attributes['city_id']);

            $query->where(function ($subQuery) use ($attributes, $city): void {
                $subQuery->where('city_id', (int) $attributes['city_id']);

                if ($city) {
                    $subQuery->orWhereRaw('LOWER(city) LIKE ?', ['%'.mb_strtolower($city->name).'%']);
                }
            });
        }

        if (filled($attributes['commune_id'] ?? null)) {
            $commune = Commune::query()->find($attributes['commune_id']);

            $query->where(function ($subQuery) use ($attributes, $commune): void {
                $subQuery->where('commune_id', (int) $attributes['commune_id']);

                if ($commune) {
                    $subQuery->orWhereRaw('LOWER(commune) LIKE ?', ['%'.mb_strtolower($commune->name).'%']);
                }
            });
        }

        if (filled($attributes['neighborhood_id'] ?? null)) {
            $neighborhood = Neighborhood::query()->find($attributes['neighborhood_id']);

            if ($neighborhood) {
                $needle = '%'.mb_strtolower($neighborhood->name).'%';

                $query->where(function ($subQuery) use ($needle): void {
                    $subQuery
                        ->whereRaw('LOWER(address) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(commune) LIKE ?', [$needle])
                        ->orWhereHas('meters', fn ($meterQuery) => $meterQuery->whereRaw('LOWER(neighborhood) LIKE ?', [$needle]))
                        ->orWhereHas('incidentReports', fn ($reportQuery) => $reportQuery->whereRaw('LOWER(address) LIKE ?', [$needle]));
                });
            }
        }

        if (filled($attributes['sub_neighborhood_id'] ?? null)) {
            $subNeighborhood = SubNeighborhood::query()->find($attributes['sub_neighborhood_id']);

            if ($subNeighborhood) {
                $needle = '%'.mb_strtolower($subNeighborhood->name).'%';

                $query->where(function ($subQuery) use ($needle): void {
                    $subQuery
                        ->whereRaw('LOWER(address) LIKE ?', [$needle])
                        ->orWhereHas('meters', fn ($meterQuery) => $meterQuery->whereRaw('LOWER(sub_neighborhood) LIKE ?', [$needle]))
                        ->orWhereHas('incidentReports', function ($reportQuery) use ($needle): void {
                            $reportQuery
                                ->whereRaw('LOWER(address) LIKE ?', [$needle])
                                ->orWhereHas('meter', fn ($meterQuery) => $meterQuery->whereRaw('LOWER(sub_neighborhood) LIKE ?', [$needle]));
                        });
                });
            }
        }

        if ($this->hasReportFilters($attributes)) {
            $signalType = filled($attributes['signal_type_id'] ?? null)
                ? SignalType::query()->find($attributes['signal_type_id'])
                : null;

            $query->whereHas('incidentReports', function ($reportQuery) use ($attributes, $signalType): void {
                if ($signalType) {
                    $reportQuery->where('signal_code', $signalType->code);
                }

                if (filled($attributes['report_resolution_status'] ?? null)) {
                    if ($attributes['report_resolution_status'] === 'resolved') {
                        $reportQuery->where('status', IncidentReportStatus::Resolved->value);
                    } else {
                        $reportQuery->whereIn('status', [
                            IncidentReportStatus::Submitted->value,
                            IncidentReportStatus::InProgress->value,
                        ]);
                    }
                }

                if (filled($attributes['report_date_from'] ?? null)) {
                    $reportQuery->where('created_at', '>=', Carbon::parse($attributes['report_date_from'])->startOfDay());
                }

                if (filled($attributes['report_date_to'] ?? null)) {
                    $reportQuery->where('created_at', '<=', Carbon::parse($attributes['report_date_to'])->endOfDay());
                }

                if (filled($attributes['organization_type_id'] ?? null)) {
                    $organizationTypeId = (int) $attributes['organization_type_id'];

                    $reportQuery->whereHas('organization', fn ($organizationQuery) => $organizationQuery->where('organization_type_id', $organizationTypeId));
                }
            });
        }
    }

    private function hasReportFilters(array $attributes): bool
    {
        return collect([
            $attributes['signal_type_id'] ?? null,
            $attributes['organization_type_id'] ?? null,
            $attributes['report_resolution_status'] ?? null,
            $attributes['report_date_from'] ?? null,
            $attributes['report_date_to'] ?? null,
        ])->contains(fn ($value) => filled($value));
    }

    private function targetFilters(array $attributes): array
    {
        return collect([
            'city_id' => $attributes['city_id'] ?? null,
            'commune_id' => $attributes['commune_id'] ?? null,
            'neighborhood_id' => $attributes['neighborhood_id'] ?? null,
            'sub_neighborhood_id' => $attributes['sub_neighborhood_id'] ?? null,
            'signal_type_id' => $attributes['signal_type_id'] ?? null,
            'organization_type_id' => $attributes['organization_type_id'] ?? null,
            'report_resolution_status' => $attributes['report_resolution_status'] ?? null,
            'report_date_from' => $attributes['report_date_from'] ?? null,
            'report_date_to' => $attributes['report_date_to'] ?? null,
        ])->filter(fn ($value) => filled($value))->all();
    }
}
