<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_assignments', function (Blueprint $table): void {
            $table->string('assignment_source', 30)->default('personal')->index()->after('is_primary');
        });

        Schema::create('purchase_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->string('material_name', 160);
            $table->date('purchase_date');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['public_user_id', 'purchase_date']);
        });

        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->foreignId('purchase_receipt_id')->nullable()->after('damage_attachment')->constrained('purchase_receipts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('purchase_receipt_id');
        });

        Schema::dropIfExists('purchase_receipts');

        Schema::table('meter_assignments', function (Blueprint $table): void {
            $table->dropColumn('assignment_source');
        });
    }
};
