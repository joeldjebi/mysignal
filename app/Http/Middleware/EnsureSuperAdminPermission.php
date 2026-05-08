<?php

namespace App\Http\Middleware;

use App\Support\Auth\SuperAdminAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminPermission
{
    public function handle(Request $request, Closure $next, string $permissionCode, string ...$alternatePermissionCodes): Response
    {
        $user = $request->user()?->loadMissing(['permissions', 'roles.permissions']);

        if (! $user || $user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver = app(SuperAdminAccessResolver::class);
        $access = $request->attributes->get('super_admin_access') ?? $resolver->resolve($user);

        if ($access === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver->apply($user, $access);
        $request->attributes->set('super_admin_access', $access);

        if ($user->is_super_admin) {
            return $next($request);
        }

        $permissionCodes = $user->effectivePermissionCodes();

        $allowedPermissionCodes = collect([$permissionCode, ...$alternatePermissionCodes]);

        if ($allowedPermissionCodes->intersect($permissionCodes)->isEmpty()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
