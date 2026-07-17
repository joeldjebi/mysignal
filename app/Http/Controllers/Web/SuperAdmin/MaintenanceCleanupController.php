<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Services\Maintenance\DatabaseCleanupService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\View\View;

class MaintenanceCleanupController extends Controller
{
    private const PUBLIC_NEARBY_REPORT_NOTIFICATIONS_FEATURE = 'PUBLIC_NEARBY_REPORT_NOTIFICATIONS';

    public function index(DatabaseCleanupService $cleanupService): View
    {
        $profiles = collect($cleanupService->profiles())
            ->map(function (array $profile) use ($cleanupService): array {
                $profile['counts'] = $cleanupService->countsForProfile($profile['code']);
                $profile['display_counts'] = collect($profile['counts'])
                    ->map(fn (int $count, string $table): array => [
                        'name' => $table,
                        'label' => $this->tableLabel($table, $cleanupService->tables()),
                        'count' => $count,
                    ])
                    ->values()
                    ->all();
                $profile['rows_count'] = array_sum($profile['counts']);

                return $profile;
            })
            ->all();

        return view('super-admin.maintenance.cleanup', [
            'profiles' => $profiles,
            'tables' => $cleanupService->tables(),
            'cleanupEnabled' => (bool) config('app.maintenance_cleanup_enabled'),
            'confirmationText' => DatabaseCleanupService::CONFIRMATION,
            'nearbyReportNotificationsFeature' => $this->nearbyReportNotificationsFeature(),
        ]);
    }

    private function tableLabel(string $table, array $tables): string
    {
        return $tables[$table]['label'] ?? Str::of($table)->replace('_', ' ')->title()->toString();
    }

    public function toggleNearbyReportNotifications(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $feature = $this->nearbyReportNotificationsFeature();
        $nextStatus = $feature->status === 'active' ? 'inactive' : 'active';

        $feature->update([
            'status' => $nextStatus,
        ]);

        $activityLogger->log(
            'maintenance.feature.toggled',
            'Statut de la fonctionnalite de notification UP de proximite modifie depuis la maintenance.',
            'feature',
            [
                'feature_code' => self::PUBLIC_NEARBY_REPORT_NOTIFICATIONS_FEATURE,
                'status' => $nextStatus,
            ],
            $request,
            portal: 'super_admin',
        );

        return redirect()
            ->route('super-admin.maintenance.cleanup.index')
            ->with('success', 'La fonctionnalite de notification UP de proximite a ete '.($nextStatus === 'active' ? 'activee.' : 'desactivee.'));
    }

    public function destroy(Request $request, DatabaseCleanupService $cleanupService, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless((bool) config('app.maintenance_cleanup_enabled'), 403);

        $attributes = $request->validate([
            'profile' => ['required', 'string'],
            'confirmation' => ['required', 'string', 'in:'.DatabaseCleanupService::CONFIRMATION],
        ]);

        try {
            $result = $cleanupService->cleanup($attributes['profile']);
        } catch (QueryException $exception) {
            report($exception);

            return back()->withErrors([
                'cleanup' => 'Nettoyage impossible: certaines tables liees doivent etre videes avant.',
            ]);
        }

        $activityLogger->log(
            'maintenance.cleanup.executed',
            'Nettoyage de tables execute depuis le portail super admin.',
            'maintenance_cleanup',
            [
                'profile' => $result['profile']['code'],
                'label' => $result['profile']['label'],
                'tables' => $result['profile']['tables'],
                'before' => $result['before'],
                'after' => $result['after'],
                'deleted_rows' => $result['deleted_rows'],
            ],
            $request,
            portal: 'super_admin',
        );

        return redirect()
            ->route('super-admin.maintenance.cleanup.index')
            ->with('success', number_format($result['deleted_rows'], 0, ',', ' ').' ligne(s) videe(s) pour le profil '.$result['profile']['label'].'.');
    }

    public function destroyTable(Request $request, DatabaseCleanupService $cleanupService, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless((bool) config('app.maintenance_cleanup_enabled'), 403);

        $attributes = $request->validate([
            'table' => ['required', 'string'],
            'confirmation' => ['required', 'string', 'in:'.DatabaseCleanupService::CONFIRMATION],
            'include_dependencies' => ['nullable', 'boolean'],
        ]);

        try {
            $result = $cleanupService->cleanupTable($attributes['table'], (bool) ($attributes['include_dependencies'] ?? false));
        } catch (InvalidArgumentException $exception) {
            $dependentTables = $cleanupService->dependentTablesFor($attributes['table']);

            return back()->withErrors([
                'cleanup' => $dependentTables !== []
                    ? 'Cette table ne peut pas etre videe seule. Cochez "Inclure les dependances" ou videz d abord les tables dependantes non vides: '.implode(', ', $dependentTables).'.'
                    : $exception->getMessage(),
            ]);
        } catch (QueryException $exception) {
            report($exception);

            $dependentTables = $cleanupService->dependentTablesFor($attributes['table']);
            $message = 'Cette table ne peut pas etre videe seule car elle est encore liee a d autres donnees.';

            if ($dependentTables !== []) {
                $message .= ' Videz d abord les tables dependantes: '.implode(', ', $dependentTables).'.';
            } else {
                $message .= ' Videz d abord les tables dependantes.';
            }

            return back()->withErrors([
                'cleanup' => $message,
            ]);
        }

        $activityLogger->log(
            'maintenance.cleanup.table_executed',
            'Nettoyage individuel de table execute depuis le portail super admin.',
            'maintenance_cleanup',
            [
                'table' => $result['table'],
                'tables' => $result['tables'],
                'dependent_tables' => $result['dependent_tables'],
                'before' => $result['before'],
                'after' => $result['after'],
                'deleted_rows' => $result['deleted_rows'],
            ],
            $request,
            portal: 'super_admin',
        );

        return redirect()
            ->route('super-admin.maintenance.cleanup.index')
            ->with('success', 'Nettoyage termine pour '.$result['table'].' : '.number_format($result['deleted_rows'], 0, ',', ' ').' ligne(s) supprimee(s) sur '.count($result['tables']).' table(s).');
    }

    private function nearbyReportNotificationsFeature(): Feature
    {
        return Feature::query()->firstOrCreate(
            ['code' => self::PUBLIC_NEARBY_REPORT_NOTIFICATIONS_FEATURE],
            [
                'name' => 'Notifications UP de proximite',
                'description' => 'Envoie une notification aux UP situes dans un rayon de 1 km lorsqu un signalement compatible est cree.',
                'status' => 'active',
            ],
        );
    }
}
