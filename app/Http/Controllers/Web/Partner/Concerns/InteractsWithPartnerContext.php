<?php

namespace App\Http\Controllers\Web\Partner\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

trait InteractsWithPartnerContext
{
    protected function partnerContext(): array
    {
        $user = auth()->user()?->loadMissing(['organization.organizationType', 'creator', 'permissions', 'roles.permissions']);
        $organization = $user?->organization;
        $permissionCodes = collect($user?->permissions?->pluck('code')->all() ?? [])
            ->merge(collect($user?->roles ?? [])->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();
        $isPartnerRootAdmin = (bool) ($user?->creator?->is_super_admin);

        return [
            'user' => $user,
            'organization' => $organization,
            'permission_codes' => $permissionCodes->all(),
            'is_partner_root_admin' => $isPartnerRootAdmin,
        ];
    }

    protected function partnerAuthorizationFlags(?User $user = null): array
    {
        $context = $this->partnerContextFor($user);

        return [
            'canViewDashboard' => $context['is_partner_root_admin'] || $context['permission_codes']->contains('PARTNER_DASHBOARD_VIEW'),
            'canManageOffers' => $context['is_partner_root_admin'] || $context['permission_codes']->contains('PARTNER_DISCOUNT_OFFERS_MANAGE'),
            'canViewHistory' => $context['is_partner_root_admin'] || $context['permission_codes']->contains('PARTNER_DISCOUNT_HISTORY_VIEW'),
            'canManageUsers' => $context['is_partner_root_admin'] || $context['permission_codes']->contains('PARTNER_USERS_MANAGE'),
            'canCreateUsers' => $context['is_partner_root_admin'] || $context['permission_codes']->contains('PARTNER_USERS_CREATE'),
            'canUpdateUsers' => $context['is_partner_root_admin'] || $context['permission_codes']->contains('PARTNER_USERS_UPDATE'),
            'canToggleUsers' => $context['is_partner_root_admin'] || $context['permission_codes']->contains('PARTNER_USERS_TOGGLE_STATUS'),
        ];
    }

    protected function partnerRoles(): Collection
    {
        return Role::query()
            ->whereNull('organization_id')
            ->where('status', 'active')
            ->whereIn('code', ['PARTNER_ADMIN', 'PARTNER_MANAGER', 'PARTNER_AGENT'])
            ->orderBy('name')
            ->get();
    }

    protected function partnerPermissionCatalog(): Collection
    {
        return Permission::query()
            ->where('status', 'active')
            ->whereIn('code', [
                'PARTNER_ACCESS_PORTAL',
                'PARTNER_DASHBOARD_VIEW',
                'PARTNER_DISCOUNT_SCAN',
                'PARTNER_DISCOUNT_APPLY',
                'PARTNER_DISCOUNT_HISTORY_VIEW',
                'PARTNER_DISCOUNT_OFFERS_MANAGE',
                'PARTNER_USERS_MANAGE',
                'PARTNER_USERS_CREATE',
                'PARTNER_USERS_UPDATE',
                'PARTNER_USERS_TOGGLE_STATUS',
            ])
            ->orderBy('name')
            ->get();
    }

    private function partnerContextFor(?User $user = null): array
    {
        $user = ($user ?? auth()->user())?->loadMissing(['creator', 'permissions', 'roles.permissions']);
        $permissionCodes = collect($user?->permissions?->pluck('code')->all() ?? [])
            ->merge(collect($user?->roles ?? [])->flatMap(fn ($role) => $role->permissions->pluck('code')))
            ->unique()
            ->values();

        return [
            'user' => $user,
            'permission_codes' => $permissionCodes,
            'is_partner_root_admin' => (bool) ($user?->creator?->is_super_admin),
        ];
    }
}
