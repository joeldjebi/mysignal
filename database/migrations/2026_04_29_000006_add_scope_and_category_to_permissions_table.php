<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table): void {
            $table->string('profile_scope', 40)->default('all')->after('description')->index();
            $table->string('category', 60)->default('other')->after('profile_scope')->index();
        });

        DB::table('permissions')
            ->select(['id', 'code'])
            ->orderBy('id')
            ->get()
            ->each(function ($permission): void {
                $code = strtoupper((string) $permission->code);

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update([
                        'profile_scope' => Permission::inferProfileScope($code),
                        'category' => Permission::inferCategory($code),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropColumn(['profile_scope', 'category']);
        });
    }
};
