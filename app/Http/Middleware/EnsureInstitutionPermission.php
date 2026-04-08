<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstitutionPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permissionCode): Response
    {
        $user = $request->user()?->loadMissing(['creator', 'permissions', 'roles.permissions']);

        if (! $user || $user->organization_id === null || $user->is_super_admin) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user->creator?->is_super_admin) {
            return $next($request);
        }

        $permissionCodes = $user->permissions
            ->pluck('code')
            ->merge($user->roles->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique();

        if (! $permissionCodes->contains($permissionCode)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
