<?php

namespace App\Http\Middleware;

use App\Support\Auth\PartnerAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin || $user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver = app(PartnerAccessResolver::class);
        $access = $resolver->resolve($user);

        if ($access === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver->apply($user, $access);
        $request->attributes->set('partner_access', $access);
        $request->attributes->set('partner_organization_id', $access->organization_id);

        return $next($request);
    }
}
