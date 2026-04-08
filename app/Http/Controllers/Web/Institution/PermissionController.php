<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use Illuminate\View\View;

class PermissionController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);

        $permissions = $this->institutionPermissions($context['feature_codes'])
            ->loadCount([
                'users as institution_users_count' => fn ($query) => $query->where('organization_id', $organization->id),
                'roles as institution_roles_count' => fn ($query) => $query->where('organization_id', $organization->id),
            ]);

        return view('institution/permissions/index', [
            'organization' => $organization,
            'features' => $context['feature_codes'],
            'activeNav' => 'permissions',
            'permissions' => $permissions,
            'groupedPermissions' => $permissions->groupBy(function ($permission): string {
                return match (true) {
                    str_starts_with($permission->code, 'INSTITUTION_DASHBOARD_') => 'Dashboard institutionnel',
                    str_starts_with($permission->code, 'INSTITUTION_') => 'Acces institutionnels',
                    str_starts_with($permission->code, 'PUBLIC_') => 'Modules publics',
                    default => 'Autres permissions',
                };
            })->sortKeys(),
        ]);
    }
}
