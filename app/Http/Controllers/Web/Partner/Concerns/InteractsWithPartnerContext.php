<?php

namespace App\Http\Controllers\Web\Partner\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Auth\PartnerAccessResolver;
use Illuminate\Support\Collection;

trait InteractsWithPartnerContext
{
    protected function partnerContext(): array
    {
        $user = auth()->user()?->loadMissing(['creator', 'permissions', 'roles.permissions']);

        if ($user instanceof User) {
            $resolver = app(PartnerAccessResolver::class);
            $access = request()->attributes->get('partner_access') ?? $resolver->resolve($user);

            if ($access !== null) {
                $resolver->apply($user, $access);
                request()->attributes->set('partner_access', $access);
                request()->attributes->set('partner_organization_id', $access->organization_id);
            }
        }

        $organization = $user?->organization;
        $permissionCodes = $user?->effectivePermissionCodes() ?? collect();
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

        if ($user instanceof User) {
            $resolver = app(PartnerAccessResolver::class);
            $access = request()->attributes->get('partner_access') ?? $resolver->resolve($user);

            if ($access !== null) {
                $resolver->apply($user, $access);
                request()->attributes->set('partner_access', $access);
                request()->attributes->set('partner_organization_id', $access->organization_id);
            }
        }

        $permissionCodes = $user?->effectivePermissionCodes() ?? collect();

        return [
            'user' => $user,
            'permission_codes' => $permissionCodes,
            'is_partner_root_admin' => (bool) ($user?->creator?->is_super_admin),
        ];
    }
}
