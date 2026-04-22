<?php

namespace App\Domain\Partners\Actions;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePartnerUserAction
{
    public function handle(User $actor, User $target, array $payload): User
    {
        $this->assertSamePartnerOrganization($actor, $target);
        $role = $this->resolveRole((string) $payload['role_code']);

        return DB::transaction(function () use ($target, $payload, $role): User {
            $attributes = [
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'status' => $payload['status'],
            ];

            if (filled($payload['password'] ?? null)) {
                $attributes['password'] = Hash::make($payload['password']);
            }

            $target->update($attributes);
            $target->roles()->sync([$role->id]);
            $target->permissions()->sync([]);

            return $target->fresh(['organization.organizationType', 'roles.permissions']);
        });
    }

    private function assertSamePartnerOrganization(User $actor, User $target): void
    {
        $actor->loadMissing('organization.organizationType');
        $target->loadMissing('organization.organizationType');

        if (
            $actor->organization_id === null ||
            $target->organization_id === null ||
            $actor->organization_id !== $target->organization_id ||
            $actor->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT' ||
            $target->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT'
        ) {
            throw ValidationException::withMessages([
                'user' => ['Cet utilisateur n appartient pas a votre etablissement partenaire.'],
            ]);
        }
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
