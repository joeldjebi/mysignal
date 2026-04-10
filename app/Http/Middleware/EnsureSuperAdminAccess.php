<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user()?->loadMissing(['permissions', 'roles.permissions']);

        if (! $user || $user->organization_id !== null || $user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user->is_super_admin) {
            return $next($request);
        }

        $permissionCodes = $user->permissions
            ->pluck('code')
            ->merge($user->roles->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique();

        if (! $permissionCodes->contains('SA_ACCESS_PORTAL')) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
