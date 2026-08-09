<?php

use App\Models\IncidentReport;
use App\Support\Audit\ActivityLogger;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
$middleware->append(\App\Http\Middleware\PrometheusMiddleware::class);
        $middleware->redirectTo(
            guests: function (Request $request): string {
                if ($request->is('sa') || $request->is('sa/*')) {
                    return route('super-admin.login');
                }

                if ($request->is('backoffice') || $request->is('backoffice/*')) {
                    return route('backoffice.login');
                }

                if ($request->is('institution') || $request->is('institution/*')) {
                    return route('institution.login');
                }

                if ($request->is('partner') || $request->is('partner/*')) {
                    return route('partner.login');
                }

                return route('public.landing');
            },
            users: function (Request $request): string {
                $user = $request->user();

                if ($user?->is_super_admin) {
                    return route('super-admin.dashboard');
                }

                if ($user?->organization_id === null && $user?->hasPermissionCode('SA_ACCESS_PORTAL')) {
                    if ($user?->hasPermissionCode('SA_DASHBOARD_VIEW')) {
                        return route('super-admin.dashboard');
                    }

                    foreach ([
                        'SA_SYSTEM_USERS_MANAGE' => 'super-admin.system-users.index',
                        'SA_REPARATION_CASES_MANAGE' => 'super-admin.reparation-cases.index',
                        'SA_PAYMENTS_VIEW' => 'super-admin.payments.index',
                        'SA_ACTIVITY_LOGS_VIEW_SELF' => 'super-admin.activity-logs.index',
                        'SA_ACTIVITY_LOGS_VIEW_INSTITUTION' => 'super-admin.activity-logs.index',
                        'SA_ACTIVITY_LOGS_VIEW_PUBLIC' => 'super-admin.activity-logs.index',
                        'SA_ACTIVITY_LOGS_VIEW_INTERNAL' => 'super-admin.activity-logs.index',
                        'SA_PUBLIC_USERS_MANAGE' => 'super-admin.public-users.index',
                        'SA_PUBLIC_REPORTS_VIEW' => 'super-admin.public-reports.index',
                        'SA_ORGANIZATIONS_MANAGE' => 'super-admin.organizations.index',
                        'SA_APPLICATIONS_MANAGE' => 'super-admin.applications.index',
                        'SA_ROLES_MANAGE' => 'super-admin.roles.index',
                        'SA_PERMISSIONS_MANAGE' => 'super-admin.permissions.index',
                    ] as $permissionCode => $routeName) {
                        if ($user?->hasPermissionCode($permissionCode)) {
                            return route($routeName);
                        }
                    }

                    return route('backoffice.home');
                }

                if ($user?->organization_id !== null) {
                    $user->loadMissing('organization.organizationType');

                    if ($user?->organization?->organizationType?->code === 'PARTNER_ESTABLISHMENT') {
                        return route('partner.dashboard');
                    }

                    return route('institution.dashboard');
                }

                return route('public.landing');
            },
        );

        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'super_admin_access' => \App\Http\Middleware\EnsureSuperAdminAccess::class,
            'super_admin_permission' => \App\Http\Middleware\EnsureSuperAdminPermission::class,
            'institution_admin' => \App\Http\Middleware\EnsureInstitutionAdmin::class,
            'institution_feature' => \App\Http\Middleware\EnsureInstitutionFeature::class,
            'institution_permission' => \App\Http\Middleware\EnsureInstitutionPermission::class,
            'partner_access' => \App\Http\Middleware\EnsurePartnerAccess::class,
            'partner_web_permission' => \App\Http\Middleware\EnsurePartnerWebPermission::class,
            'partner_user' => \App\Http\Middleware\EnsurePartnerUser::class,
            'partner_permission' => \App\Http\Middleware\EnsurePartnerPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (PostTooLargeException $exception): void {
            $request = request();

            if (! $request instanceof Request || ! $request->is('api/v1/public/reports')) {
                return;
            }

            try {
                app(ActivityLogger::class)->log(
                    'public.report.upload_too_large',
                    'Le fichier du signalement a été rejeté avant la validation Laravel.',
                    IncidentReport::class,
                    [
                        'cause' => 'post_max_size ou upload_max_filesize trop bas côté PHP/serveur.',
                        'limits' => [
                            'upload_max_filesize' => ini_get('upload_max_filesize'),
                            'post_max_size' => ini_get('post_max_size'),
                            'max_input_time' => ini_get('max_input_time'),
                            'max_execution_time' => ini_get('max_execution_time'),
                        ],
                        'content_length' => $request->server('CONTENT_LENGTH'),
                        'content_type' => $request->server('CONTENT_TYPE'),
                    ],
                    $request,
                    $request->user('public_api'),
                    'public'
                );
            } catch (Throwable) {
                //
            }
        });

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if (! $request->is('api/v1/public/reports') && ! $request->is('api/v1/public/reports/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Le fichier envoyé est trop volumineux pour être reçu par le serveur.',
                'errors' => [
                    'signal_attachment' => [
                        'Le fichier dépasse la limite actuellement acceptée par le serveur. Réessayez avec une photo de 20 Mo maximum ou une vidéo de 100 Mo maximum.',
                    ],
                ],
            ], 413);
        });
    })->create();
