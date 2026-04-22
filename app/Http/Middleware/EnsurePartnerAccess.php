<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user()?->loadMissing('organization.organizationType');

        if (! $user || $user->is_super_admin || $user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user->organization_id === null || $user->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT') {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
