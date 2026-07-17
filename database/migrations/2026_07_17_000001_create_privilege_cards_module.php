<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_users', function (Blueprint $table): void {
            $table->string('profile_photo_path')->nullable()->after('email');
        });

        Schema::create('privilege_card_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 120);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('FCFA');
            $table->json('benefits')->nullable();
            $table->unsignedSmallInteger('duration_months')->default(12);
            $table->string('status', 30)->default('active')->index();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('privilege_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('privilege_card_type_id')->constrained('privilege_card_types')->restrictOnDelete();
            $table->string('card_uuid', 36)->unique();
            $table->string('card_number', 50)->unique();
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['public_user_id', 'status']);
            $table->index('expires_at');
        });

        Schema::create('privilege_card_payment_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('privilege_card_type_id')->constrained('privilege_card_types')->restrictOnDelete();
            $table->foreignId('privilege_card_id')->nullable()->constrained('privilege_cards')->nullOnDelete();
            $table->string('sync_ref', 60)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('FCFA');
            $table->string('status', 30)->default('pending')->index();
            $table->string('provider', 40)->default('fineopay');
            $table->string('provider_reference')->nullable();
            $table->text('checkout_link')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['public_user_id', 'status']);
        });

        DB::table('privilege_card_types')->insert([
            [
                'code' => 'STANDARD',
                'name' => 'Standard',
                'price' => 1000,
                'currency' => 'FCFA',
                'benefits' => json_encode(['Acces aux avantages standards', 'Carte privilege digitale']),
                'duration_months' => 12,
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PREMIUM',
                'name' => 'Premium',
                'price' => 5000,
                'currency' => 'FCFA',
                'benefits' => json_encode(['Avantages standards inclus', 'Acces aux offres premium']),
                'duration_months' => 12,
                'status' => 'active',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GOLD',
                'name' => 'Gold',
                'price' => 10000,
                'currency' => 'FCFA',
                'benefits' => json_encode(['Avantages premium inclus', 'Priorite sur les offres Gold']),
                'duration_months' => 12,
                'status' => 'active',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('features')->updateOrInsert(
            ['code' => 'PRIVILEGE_CARDS_MANAGE'],
            [
                'name' => 'Gestion des cartes privileges',
                'description' => 'Permet aux acteurs autorises de creer et gerer les cartes privileges.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ([
            ['SA_PRIVILEGE_CARD_TYPES_MANAGE', 'Gerer cartes privileges', 'Permet de parametrer les cartes privileges.'],
            ['SA_PRIVILEGE_CARD_TYPES_VIEW', 'Voir cartes privileges', 'Permet de consulter les cartes privileges.'],
            ['SA_PRIVILEGE_CARD_TYPES_CREATE', 'Creer cartes privileges', 'Permet de creer des cartes privileges.'],
            ['SA_PRIVILEGE_CARD_TYPES_UPDATE', 'Modifier cartes privileges', 'Permet de modifier des cartes privileges.'],
            ['SA_PRIVILEGE_CARD_TYPES_DELETE', 'Supprimer cartes privileges', 'Permet de supprimer des cartes privileges.'],
            ['SA_PRIVILEGE_CARD_TYPES_TOGGLE_STATUS', 'Activer ou desactiver cartes privileges', 'Permet de changer le statut des cartes privileges.'],
        ] as [$code, $name, $description]) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'profile_scope' => 'super_admin',
                    'category' => 'payments',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('privilege_card_payment_sessions');
        Schema::dropIfExists('privilege_cards');
        Schema::dropIfExists('privilege_card_types');

        Schema::table('public_users', function (Blueprint $table): void {
            $table->dropColumn('profile_photo_path');
        });
    }
};
