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

                if ($user?->organization_id !== null) {
                    return route('institution.dashboard');
                }

                return route('public.landing');
            },
        );

        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'institution_admin' => \App\Http\Middleware\EnsureInstitutionAdmin::class,
            'institution_feature' => \App\Http\Middleware\EnsureInstitutionFeature::class,
            'institution_permission' => \App\Http\Middleware\EnsureInstitutionPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
