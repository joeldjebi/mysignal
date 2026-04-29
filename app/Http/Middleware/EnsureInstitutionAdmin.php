<?php

namespace App\Http\Middleware;

use App\Support\Auth\InstitutionAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstitutionAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_super_admin || $user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver = app(InstitutionAccessResolver::class);
        $access = $resolver->resolve($user);

        if ($access === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $resolver->apply($user, $access);
        $request->attributes->set('institution_access', $access);
        $request->attributes->set('institution_organization_id', $access->organization_id);

        return $next($request);
    }
}
