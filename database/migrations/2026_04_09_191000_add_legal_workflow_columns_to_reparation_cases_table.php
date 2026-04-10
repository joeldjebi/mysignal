<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparation_cases', function (Blueprint $table): void {
            $table->string('case_type', 40)->default('precontentieux')->after('reference');
            $table->string('priority', 30)->default('normal')->after('case_type');
            $table->foreignId('bailiff_user_id')->nullable()->after('assigned_to_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('lawyer_user_id')->nullable()->after('bailiff_user_id')->constrained('users')->nullOnDelete();
            $table->text('closure_reason')->nullable()->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('reparation_cases', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('lawyer_user_id');
            $table->dropConstrainedForeignId('bailiff_user_id');
            $table->dropColumn([
                'case_type',
                'priority',
                'closure_reason',
            ]);
        });
    }
};
