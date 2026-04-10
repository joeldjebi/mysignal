<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_user_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pricing_rule_id')->constrained()->restrictOnDelete();
            $table->string('code', 60)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('profile_kind', 30)->default('individual')->index();
            $table->string('status', 30)->default('active')->index();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::table('public_users', function (Blueprint $table): void {
            $table->foreignId('public_user_type_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('company_name')->nullable()->after('email');
            $table->string('company_registration_number', 120)->nullable()->after('company_name');
            $table->string('tax_identifier', 120)->nullable()->after('company_registration_number');
            $table->string('business_sector', 120)->nullable()->after('tax_identifier');
            $table->string('company_address')->nullable()->after('business_sector');
        });

        $existingPricing = DB::table('pricing_rules')->where('code', 'public_signal_report')->first();
        $defaultAmount = (int) ($existingPricing->amount ?? 100);
        $defaultCurrency = (string) ($existingPricing->currency ?? 'FCFA');
        $defaultStartsAt = $existingPricing->starts_at ?? now();

        DB::table('pricing_rules')->updateOrInsert(
            ['code' => 'public_up_standard'],
            [
                'label' => 'Tarification usager public',
                'amount' => $defaultAmount,
                'currency' => $defaultCurrency,
                'status' => 'active',
                'starts_at' => $defaultStartsAt,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        DB::table('pricing_rules')->updateOrInsert(
            ['code' => 'public_upe_standard'],
            [
                'label' => 'Tarification usager public entreprise',
                'amount' => $defaultAmount,
                'currency' => $defaultCurrency,
                'status' => 'active',
                'starts_at' => $defaultStartsAt,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $pricingRuleIds = DB::table('pricing_rules')->pluck('id', 'code');

        DB::table('public_user_types')->updateOrInsert(
            ['code' => 'UP'],
            [
                'pricing_rule_id' => $pricingRuleIds['public_up_standard'],
                'name' => 'Usager public',
                'description' => 'Compte public classique pour les particuliers.',
                'profile_kind' => 'individual',
                'status' => 'active',
                'sort_order' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        DB::table('public_user_types')->updateOrInsert(
            ['code' => 'UPE'],
            [
                'pricing_rule_id' => $pricingRuleIds['public_upe_standard'],
                'name' => 'Usager public entreprise',
                'description' => 'Compte public dedie aux entreprises et structures professionnelles.',
                'profile_kind' => 'business',
                'status' => 'active',
                'sort_order' => 2,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $defaultPublicUserTypeId = DB::table('public_user_types')->where('code', 'UP')->value('id');

        DB::table('public_users')
            ->whereNull('public_user_type_id')
            ->update(['public_user_type_id' => $defaultPublicUserTypeId]);

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE public_users ALTER COLUMN public_user_type_id SET NOT NULL'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE public_users MODIFY public_user_type_id BIGINT UNSIGNED NOT NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        Schema::table('public_users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('public_user_type_id');
            $table->dropColumn([
                'company_name',
                'company_registration_number',
                'tax_identifier',
                'business_sector',
                'company_address',
            ]);
        });

        Schema::dropIfExists('public_user_types');
    }
};
