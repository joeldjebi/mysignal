<?php

namespace Database\Seeders\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminRoleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $portalAccessPermission = Permission::query()
            ->where('code', 'SA_ACCESS_PORTAL')
            ->first();

        $superAdmin = User::query()
            ->where('is_super_admin', true)
            ->orderBy('id')
            ->first();

        Role::query()
            ->whereNull('organization_id')
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->each(function (Role $role) use ($portalAccessPermission, $superAdmin): void {
                $email = strtolower($role->code).'@backoffice.local';

                $user = User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'organization_id' => null,
                        'name' => 'Utilisateur '.$role->name,
                        'phone' => null,
                        'password' => Hash::make('12345678'),
                        'is_super_admin' => false,
                        'status' => 'active',
                        'created_by' => $superAdmin?->id,
                    ],
                );

                $user->roles()->sync([$role->id]);

                if ($portalAccessPermission !== null) {
                    $user->permissions()->syncWithoutDetaching([$portalAccessPermission->id]);
                }
            });
    }
}
