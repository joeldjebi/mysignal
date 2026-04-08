<?php

namespace App\Http\Middleware;

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
        $user = $request->user()?->loadMissing(['organization.application.features', 'organization.featureOverrides', 'features', 'creator', 'permissions', 'roles.permissions']);

        if (! $user) {
            abort(Response::HTTP_FORBIDDEN);
        }

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
            $permissionCodes = $user->permissions
                ->pluck('code')
                ->merge($user->roles->flatMap(fn ($role) => $role->permissions->pluck('code')))
                ->unique();

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
