<?php

namespace App\Domain\Partners\Actions;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreatePartnerUserAction
{
    public function handle(User $actor, array $payload): User
    {
        $actor->loadMissing('organization.organizationType');

        if ($actor->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT') {
            throw ValidationException::withMessages([
                'organization' => ['Votre compte n est pas rattache a un etablissement partenaire.'],
            ]);
        }

        $role = $this->resolveRole((string) $payload['role_code']);

        return DB::transaction(function () use ($actor, $payload, $role): User {
            $user = User::query()->create([
                'organization_id' => $actor->organization_id,
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'password' => Hash::make($payload['password']),
                'is_super_admin' => false,
                'status' => 'active',
                'created_by' => $actor->id,
            ]);

            $user->roles()->sync([$role->id]);
            $user->permissions()->sync([]);

            return $user->fresh(['organization.organizationType', 'roles.permissions']);
        });
    }

    private function resolveRole(string $roleCode): Role
    {
        return Role::query()
            ->whereNull('organization_id')
            ->where('status', 'active')
            ->where('code', $roleCode)
            ->firstOrFail();
    }
}
