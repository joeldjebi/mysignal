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
            $table->string('flag', 20)->nullable()->after('dial_code');
        });

        $now = now();

        $countries = [
            ['code' => 'BJ', 'name' => 'Benin', 'dial_code' => '229', 'flag' => '🇧🇯', 'status' => 'inactive'],
            ['code' => 'BF', 'name' => 'Burkina Faso', 'dial_code' => '226', 'flag' => '🇧🇫', 'status' => 'inactive'],
            ['code' => 'CV', 'name' => 'Cabo Verde', 'dial_code' => '238', 'flag' => '🇨🇻', 'status' => 'inactive'],
            ['code' => 'CI', 'name' => "Cote d'Ivoire", 'dial_code' => '225', 'flag' => '🇨🇮', 'status' => 'active'],
            ['code' => 'GM', 'name' => 'Gambie', 'dial_code' => '220', 'flag' => '🇬🇲', 'status' => 'inactive'],
            ['code' => 'GH', 'name' => 'Ghana', 'dial_code' => '233', 'flag' => '🇬🇭', 'status' => 'inactive'],
            ['code' => 'GN', 'name' => 'Guinee', 'dial_code' => '224', 'flag' => '🇬🇳', 'status' => 'inactive'],
            ['code' => 'GW', 'name' => 'Guinee-Bissau', 'dial_code' => '245', 'flag' => '🇬🇼', 'status' => 'inactive'],
            ['code' => 'LR', 'name' => 'Liberia', 'dial_code' => '231', 'flag' => '🇱🇷', 'status' => 'inactive'],
            ['code' => 'ML', 'name' => 'Mali', 'dial_code' => '223', 'flag' => '🇲🇱', 'status' => 'inactive'],
            ['code' => 'MR', 'name' => 'Mauritanie', 'dial_code' => '222', 'flag' => '🇲🇷', 'status' => 'inactive'],
            ['code' => 'NE', 'name' => 'Niger', 'dial_code' => '227', 'flag' => '🇳🇪', 'status' => 'inactive'],
            ['code' => 'NG', 'name' => 'Nigeria', 'dial_code' => '234', 'flag' => '🇳🇬', 'status' => 'inactive'],
            ['code' => 'SN', 'name' => 'Senegal', 'dial_code' => '221', 'flag' => '🇸🇳', 'status' => 'inactive'],
            ['code' => 'SL', 'name' => 'Sierra Leone', 'dial_code' => '232', 'flag' => '🇸🇱', 'status' => 'inactive'],
            ['code' => 'TG', 'name' => 'Togo', 'dial_code' => '228', 'flag' => '🇹🇬', 'status' => 'inactive'],
        ];

        foreach ($countries as $country) {
            $exists = DB::table('countries')->where('code', $country['code'])->exists();

            if ($exists) {
                DB::table('countries')
                    ->where('code', $country['code'])
                    ->update([
                        'name' => $country['name'],
                        'dial_code' => $country['dial_code'],
                        'flag' => $country['flag'],
                        'status' => $country['status'],
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('countries')->insert([
                'name' => $country['name'],
                'code' => $country['code'],
                'dial_code' => $country['dial_code'],
                'flag' => $country['flag'],
                'status' => $country['status'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            $table->dropColumn('flag');
        });
    }
};
