<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('payments', 'payment_context')) {
                $table->string('payment_context', 30)->default('report')->index()->after('provider');
            }
        });

        Schema::table('incident_report_payment_sessions', function (Blueprint $table): void {
            if (! Schema::hasColumn('incident_report_payment_sessions', 'payment_context')) {
                $table->string('payment_context', 30)->default('report')->index()->after('checkout_link');
            }

            if (! Schema::hasColumn('incident_report_payment_sessions', 'damage_payload')) {
                $table->json('damage_payload')->nullable()->after('signal_attachment');
            }

            if (! Schema::hasColumn('incident_report_payment_sessions', 'damage_attachment')) {
                $table->json('damage_attachment')->nullable()->after('damage_payload');
            }
        });

        DB::table('pricing_rules')->updateOrInsert(
            ['code' => 'public_damage_declaration'],
            [
                'label' => 'Paiement declaration de dommage',
                'amount' => 100,
                'currency' => 'FCFA',
                'status' => 'active',
                'starts_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        Schema::table('incident_report_payment_sessions', function (Blueprint $table): void {
            if (Schema::hasColumn('incident_report_payment_sessions', 'damage_attachment')) {
                $table->dropColumn('damage_attachment');
            }

            if (Schema::hasColumn('incident_report_payment_sessions', 'damage_payload')) {
                $table->dropColumn('damage_payload');
            }

            if (Schema::hasColumn('incident_report_payment_sessions', 'payment_context')) {
                $table->dropColumn('payment_context');
            }
        });

        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'payment_context')) {
                $table->dropColumn('payment_context');
            }
        });
    }
};
