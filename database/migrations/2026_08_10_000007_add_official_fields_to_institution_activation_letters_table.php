<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->string('letter_number')->nullable()->after('activation_url');
            $table->string('issue_place')->nullable()->after('letter_number');
            $table->date('issue_date')->nullable()->after('issue_place');
        });
    }

    public function down(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->dropColumn(['letter_number', 'issue_place', 'issue_date']);
        });
    }
};
