<?php

namespace App\Http\Middleware;

use App\Support\Auth\InstitutionAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstitutionFeature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        $user = $request->user()?->loadMissing(['features', 'creator', 'permissions', 'roles.permissions']);

        if (! $user) {
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
        $user->loadMissing(['organization.application.features', 'organization.featureOverrides']);

        $organizationFeatureCodes = collect($user->organization?->resolvedFeatureCodes() ?? [])
            ->unique()
            ->values();
        $directUserFeatureCodes = collect($user->features?->pluck('code')->all() ?? [])
            ->unique()
            ->values();

        if ((bool) $user->creator?->is_super_admin) {
            $effectiveFeatureCodes = $directUserFeatureCodes->isNotEmpty()
                ? $directUserFeatureCodes->unique()->values()
                : $organizationFeatureCodes;
        } else {
            $permissionCodes = $user->effectivePermissionCodes();

            $effectiveFeatureCodes = $directUserFeatureCodes
                ->merge($organizationFeatureCodes->intersect($permissionCodes))
                ->unique();
        }

        if (! $effectiveFeatureCodes->contains($featureCode)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
