<?php

namespace App\Domain\Partners\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class TogglePartnerUserStatusAction
{
    public function handle(User $actor, User $target): User
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

        if ((int) $actor->id === (int) $target->id) {
            throw ValidationException::withMessages([
                'user' => ['Vous ne pouvez pas modifier votre propre statut depuis cette operation.'],
            ]);
        }

        $target->update([
            'status' => $target->status === 'active' ? 'inactive' : 'active',
        ]);

        return $target->fresh(['organization.organizationType', 'roles.permissions']);
    }
}
