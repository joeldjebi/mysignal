<?php

namespace Database\Seeders\Admin;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstitutionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = (string) env('AI_DEFAULT_PASSWORD', '12345678');
        $superAdminId = User::query()
            ->where('is_super_admin', true)
            ->orderBy('id')
            ->value('id');

        $organizations = Organization::query()
            ->with(['application.features', 'featureOverrides'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($organizations->isEmpty()) {
            $this->command?->warn('Aucune organisation active trouvee pour creer des AI.');

            return;
        }

        $organizations->each(function (Organization $organization) use ($defaultPassword, $superAdminId): void {
            $email = $this->generatedEmail($organization);

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'organization_id' => $organization->id,
                    'name' => $this->generatedName($organization),
                    'phone' => $organization->phone,
                    'password' => Hash::make($defaultPassword),
                    'is_super_admin' => false,
                    'status' => 'active',
                    'created_by' => $superAdminId,
                ],
            );

            // Tableau vide = AI racine qui herite de tout le perimetre de son organisation.
            $user->features()->sync([]);
        });
    }

    private function generatedEmail(Organization $organization): string
    {
        $base = 'ai.'.Str::of((string) $organization->code)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->value();

        return ($base !== '' ? $base : 'ai.organization.'.$organization->id).'@mysignal.local';
    }

    private function generatedName(Organization $organization): string
    {
        return 'AI '.$organization->name;
    }
}
