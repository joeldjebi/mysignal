<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
