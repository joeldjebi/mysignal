<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->string('signature_name')->nullable()->after('letter_content');
            $table->string('signature_title')->nullable()->after('signature_name');
            $table->text('signature_content')->nullable()->after('signature_title');
        });
    }

    public function down(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->dropColumn(['signature_name', 'signature_title', 'signature_content']);
        });
    }
};
