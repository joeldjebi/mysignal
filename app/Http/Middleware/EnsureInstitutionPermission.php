<?php

namespace App\Http\Middleware;

use App\Support\Auth\InstitutionAccessResolver;
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

        if (! $user || $user->is_super_admin || $user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver = app(InstitutionAccessResolver::class);
        $access = $request->attributes->get('institution_access') ?? $resolver->resolve($user);

        if ($access === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver->apply($user, $access);
        $request->attributes->set('institution_access', $access);
        $request->attributes->set('institution_organization_id', $access->organization_id);

        if ($user->creator?->is_super_admin) {
            return $next($request);
        }

        $permissionCodes = $user->effectivePermissionCodes();

        if (! $permissionCodes->contains($permissionCode)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
