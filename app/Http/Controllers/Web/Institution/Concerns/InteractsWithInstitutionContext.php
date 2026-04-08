<?php

namespace App\Http\Controllers\Web\Institution\Concerns;

use App\Models\Commune;
use App\Models\IncidentReport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

trait InteractsWithInstitutionContext
{
    protected array $institutionAdministrationPermissionCodes = [
        'INSTITUTION_MANAGE_USERS',
        'INSTITUTION_MANAGE_ROLES',
        'INSTITUTION_MANAGE_PERMISSIONS',
    ];

    protected function institutionContext(): array
    {
        $user = auth()->user()->loadMissing(['organization.application.features', 'organization.featureOverrides', 'features', 'creator', 'permissions', 'roles.permissions']);
        $organization = $user->organization;
        $organizationFeatureCodes = collect($organization?->resolvedFeatureCodes() ?? [])
            ->unique()
            ->values();
        $directUserFeatureCodes = collect($user?->features?->pluck('code')->all() ?? [])
            ->unique()
            ->values();
        $permissionCodes = collect($user?->permissions?->pluck('code')->all() ?? [])
            ->merge(collect($user?->roles ?? [])->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
        $isInstitutionRootAdmin = (bool) ($user?->creator?->is_super_admin);
        $featureCodes = $isInstitutionRootAdmin
            ? ($directUserFeatureCodes->isNotEmpty()
                ? $directUserFeatureCodes->unique()->values()->all()
                : $organizationFeatureCodes->all())
            : $directUserFeatureCodes
                ->merge($organizationFeatureCodes->intersect($permissionCodes))
                ->unique()
                ->values()
                ->all();

        return [
            'user' => $user,
            'organization' => $organization,
            'application' => $organization?->application,
            'application_id' => $organization?->application_id,
            'organization_id' => $organization?->id,
            'network_type' => null,
            'feature_codes' => $featureCodes,
        ];
    }

    protected function institutionReportsQuery(?string $networkType, ?int $applicationId = null, ?int $organizationId = null): Builder
    {
        $query = IncidentReport::query()->with(['meter', 'publicUser', 'commune', 'assignedTo']);

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }

        if ($applicationId !== null) {
            $query->where('application_id', $applicationId);
        }

        if ($networkType !== null) {
            $query->where('network_type', $networkType);
        }

        return $query;
    }

    protected function institutionFilterState(): array
    {
        $period = (string) request('period', '14d');
        $today = now()->startOfDay();

        [$dateFrom, $dateTo] = match ($period) {
            'today' => [$today->copy(), $today->copy()->endOfDay()],
            '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30d' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [
                filled(request('date_from')) ? Carbon::parse((string) request('date_from'))->startOfDay() : now()->subDays(13)->startOfDay(),
                filled(request('date_to')) ? Carbon::parse((string) request('date_to'))->endOfDay() : now()->endOfDay(),
            ],
            default => [now()->subDays(13)->startOfDay(), now()->endOfDay()],
        };

        if ($dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        return [
            'period' => $period,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'commune_id' => filled(request('commune_id')) ? (int) request('commune_id') : null,
            'radius_km' => filled(request('radius_km')) ? (int) request('radius_km') : null,
        ];
    }

    protected function applyInstitutionFilters(Builder|QueryBuilder $query, array $filters, string $table = 'incident_reports'): Builder|QueryBuilder
    {
        $qualifiedCreatedAt = $table.'.created_at';
        $qualifiedCommuneId = $table.'.commune_id';

        $query->whereBetween($qualifiedCreatedAt, [$filters['date_from'], $filters['date_to']]);

        if ($filters['commune_id'] !== null) {
            $query->where($qualifiedCommuneId, $filters['commune_id']);
        }

        return $query;
    }

    protected function availableInstitutionCommunes(?string $networkType, ?int $applicationId = null, ?int $organizationId = null): \Illuminate\Support\Collection
    {
        return Commune::query()
            ->whereIn(
                'id',
                IncidentReport::query()
                    ->when($organizationId !== null, fn ($builder) => $builder->where('organization_id', $organizationId))
                    ->when($applicationId !== null, fn ($builder) => $builder->where('application_id', $applicationId))
                    ->when($networkType !== null, fn ($builder) => $builder->where('network_type', $networkType))
                    ->whereNotNull('commune_id')
                    ->distinct()
                    ->select('commune_id')
            )
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function institutionPermissions(array $featureCodes): \Illuminate\Support\Collection
    {
        $allowedCodes = collect($featureCodes)
            ->merge($this->institutionAdministrationPermissionCodes)
            ->unique()
            ->values()
            ->all();

        if ($allowedCodes === []) {
            return collect();
        }

        return Permission::query()
            ->where('status', 'active')
            ->whereIn('code', $allowedCodes)
            ->orderBy('name')
            ->get();
    }

    protected function groupedInstitutionPermissions(array $featureCodes): \Illuminate\Support\Collection
    {
        return $this->institutionPermissions($featureCodes)
            ->groupBy(function (Permission $permission): string {
                return match (true) {
                    str_starts_with($permission->code, 'INSTITUTION_DASHBOARD_') => 'Dashboard institutionnel',
                    str_starts_with($permission->code, 'INSTITUTION_') => 'Acces institutionnels',
                    str_starts_with($permission->code, 'PUBLIC_') => 'Modules publics',
                    default => 'Autres permissions',
                };
            })
            ->sortKeys();
    }

    protected function institutionRolesQuery(int $organizationId): Builder
    {
        return Role::query()
            ->where('organization_id', $organizationId)
            ->with('permissions');
    }

    protected function institutionAuthorizationFlags(?User $user = null): array
    {
        $user = ($user ?? auth()->user())?->loadMissing(['creator', 'permissions', 'roles.permissions']);
        $permissionCodes = collect($user?->permissions?->pluck('code')->all() ?? [])
            ->merge(collect($user?->roles ?? [])->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
        $isInstitutionRootAdmin = (bool) ($user?->creator?->is_super_admin);

        return [
            'isInstitutionRootAdmin' => $isInstitutionRootAdmin,
            'canManageInstitutionUsers' => $isInstitutionRootAdmin || $permissionCodes->contains('INSTITUTION_MANAGE_USERS'),
            'canManageInstitutionRoles' => $isInstitutionRootAdmin || $permissionCodes->contains('INSTITUTION_MANAGE_ROLES'),
            'canManageInstitutionPermissions' => $isInstitutionRootAdmin || $permissionCodes->contains('INSTITUTION_MANAGE_PERMISSIONS'),
        ];
    }

    protected function delegableInstitutionPermissions(array $featureCodes, ?User $actor = null): Collection
    {
        $actor = ($actor ?? auth()->user())?->loadMissing(['creator', 'permissions', 'roles.permissions']);

        if (! $actor) {
            return collect();
        }

        $availablePermissions = $this->institutionPermissions($featureCodes);

        if ((bool) $actor->creator?->is_super_admin) {
            return $availablePermissions;
        }

        $actorPermissionCodes = collect($actor->permissions?->pluck('code')->all() ?? [])
            ->merge(collect($actor->roles ?? [])->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();

        return $availablePermissions
            ->filter(fn (Permission $permission) => $actorPermissionCodes->contains($permission->code))
            ->values();
    }

    protected function delegableInstitutionRoles(int $organizationId, array $featureCodes, ?User $actor = null): Collection
    {
        $actor = ($actor ?? auth()->user())?->loadMissing(['creator', 'roles.permissions']);
        $roles = $this->institutionRolesQuery($organizationId)->orderBy('name')->get();

        if (! $actor) {
            return collect();
        }

        if ((bool) $actor->creator?->is_super_admin) {
            return $roles;
        }

        $delegablePermissionCodes = $this->delegableInstitutionPermissions($featureCodes, $actor)
            ->pluck('code')
            ->unique()
            ->values();

        return $roles
            ->filter(function (Role $role) use ($delegablePermissionCodes): bool {
                $rolePermissionCodes = $role->permissions->pluck('code')->unique()->values();

                return $rolePermissionCodes->diff($delegablePermissionCodes)->isEmpty();
            })
            ->values();
    }

    protected function canManageInstitutionRoleRecord(Role $role, array $featureCodes, ?User $actor = null): bool
    {
        $actor = ($actor ?? auth()->user())?->loadMissing(['creator']);

        if (! $actor) {
            return false;
        }

        if ((bool) $actor->creator?->is_super_admin) {
            return true;
        }

        return $this->delegableInstitutionRoles((int) $role->organization_id, $featureCodes, $actor)
            ->pluck('id')
            ->contains($role->id);
    }

    protected function canManageInstitutionUserRecord(User $subject, array $featureCodes, ?User $actor = null): bool
    {
        $actor = ($actor ?? auth()->user())?->loadMissing(['creator']);
        $subject->loadMissing(['roles.permissions', 'permissions']);

        if (! $actor) {
            return false;
        }

        if ((bool) $actor->creator?->is_super_admin) {
            return true;
        }

        $delegableRoleIds = $this->delegableInstitutionRoles((int) $subject->organization_id, $featureCodes, $actor)
            ->pluck('id');
        $delegablePermissionCodes = $this->delegableInstitutionPermissions($featureCodes, $actor)
            ->pluck('code');

        $subjectRoleIds = $subject->roles->pluck('id');
        $subjectPermissionCodes = $subject->permissions->pluck('code');

        return $subjectRoleIds->diff($delegableRoleIds)->isEmpty()
            && $subjectPermissionCodes->diff($delegablePermissionCodes)->isEmpty();
    }
}
