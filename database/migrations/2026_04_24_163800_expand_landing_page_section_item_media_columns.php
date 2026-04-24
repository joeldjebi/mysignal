<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE landing_page_section_items ALTER COLUMN icon TYPE VARCHAR(2048)');
        DB::statement('ALTER TABLE landing_page_section_items ALTER COLUMN url TYPE VARCHAR(2048)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE landing_page_section_items ALTER COLUMN icon TYPE VARCHAR(80)');
        DB::statement('ALTER TABLE landing_page_section_items ALTER COLUMN url TYPE VARCHAR(255)');
    }
};
