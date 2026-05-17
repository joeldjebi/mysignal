<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\DatabaseCleanupService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use InvalidArgumentException;
use Illuminate\View\View;

class MaintenanceCleanupController extends Controller
{
    public function index(DatabaseCleanupService $cleanupService): View
    {
        $profiles = collect($cleanupService->profiles())
            ->map(function (array $profile) use ($cleanupService): array {
                $profile['counts'] = $cleanupService->countsForProfile($profile['code']);
                $profile['rows_count'] = array_sum($profile['counts']);

                return $profile;
            })
            ->all();

        return view('super-admin.maintenance.cleanup', [
            'profiles' => $profiles,
            'tables' => $cleanupService->tables(),
            'cleanupEnabled' => (bool) config('app.maintenance_cleanup_enabled'),
            'confirmationText' => DatabaseCleanupService::CONFIRMATION,
        ]);
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
}
