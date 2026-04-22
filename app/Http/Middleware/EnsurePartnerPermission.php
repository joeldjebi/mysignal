<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerPermission
{
    public function handle(Request $request, Closure $next, string $permissionCode): Response
    {
        $user = $request->user('partner_api')?->loadMissing(['organization.organizationType', 'creator', 'permissions', 'roles.permissions']);

        if (! $user || $user->status !== 'active' || $user->is_super_admin || $user->organization_id === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT') {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user->creator?->is_super_admin) {
            return $next($request);
        }

        if (! $user->permissionCodes()->contains($permissionCode)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
