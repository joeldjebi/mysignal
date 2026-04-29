<?php

namespace App\Http\Middleware;

use App\Support\Auth\PartnerAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerPermission
{
    public function handle(Request $request, Closure $next, string $permissionCode): Response
    {
        $user = $request->user('partner_api')?->loadMissing(['creator', 'permissions', 'roles.permissions']);

        if (! $user || $user->status !== 'active' || $user->is_super_admin) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver = app(PartnerAccessResolver::class);
        $access = $request->attributes->get('partner_access') ?? $resolver->resolve($user);

        if ($access === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver->apply($user, $access);
        $request->attributes->set('partner_access', $access);
        $request->attributes->set('partner_organization_id', $access->organization_id);

        if ($user->creator?->is_super_admin) {
            return $next($request);
        }

        if (! $user->effectivePermissionCodes()->contains($permissionCode)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
