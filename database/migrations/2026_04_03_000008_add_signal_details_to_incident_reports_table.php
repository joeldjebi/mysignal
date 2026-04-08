<?php

use App\Domain\Payments\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->string('signal_code', 30)->nullable()->after('network_type');
            $table->string('signal_label')->nullable()->after('signal_code');
            $table->json('signal_payload')->nullable()->after('description');
            $table->unsignedSmallInteger('target_sla_hours')->nullable()->after('signal_payload');
            $table->string('payment_status', 30)->default(PaymentStatus::Pending->value)->after('status')->index();
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn([
                'signal_code',
                'signal_label',
                'signal_payload',
                'target_sla_hours',
                'payment_status',
                'paid_at',
            ]);
        });
    }
};
