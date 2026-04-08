<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            $table->string('dial_code', 4)->nullable()->after('code');
        });

        $dialCodes = [
            'CI' => '225',
            'ML' => '223',
            'BF' => '226',
            'TG' => '228',
            'BJ' => '229',
            'SN' => '221',
        ];

        foreach ($dialCodes as $countryCode => $dialCode) {
            DB::table('countries')->where('code', $countryCode)->update(['dial_code' => $dialCode]);
        }
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            $table->dropColumn('dial_code');
        });
    }
};
