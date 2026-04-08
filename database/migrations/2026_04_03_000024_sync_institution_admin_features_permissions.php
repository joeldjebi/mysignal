<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $entries = [
            [
                'code' => 'INSTITUTION_MANAGE_USERS',
                'name' => 'Administration institution - Users',
                'description' => 'Permet de creer, modifier, activer ou supprimer les users de l institution.',
            ],
            [
                'code' => 'INSTITUTION_MANAGE_ROLES',
                'name' => 'Administration institution - Roles',
                'description' => 'Permet de creer, modifier, activer ou supprimer les roles locaux de l institution.',
            ],
            [
                'code' => 'INSTITUTION_MANAGE_PERMISSIONS',
                'name' => 'Administration institution - Permissions',
                'description' => 'Permet de consulter et affecter les permissions autorisees par le super admin.',
            ],
        ];

        foreach ($entries as $entry) {
            DB::table('features')->updateOrInsert(
                ['code' => $entry['code']],
                [
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'status' => 'active',
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            DB::table('permissions')->updateOrInsert(
                ['code' => $entry['code']],
                [
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'status' => 'active',
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
