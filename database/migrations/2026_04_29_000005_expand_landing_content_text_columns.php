<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('landing_page_sections')) {
            DB::statement('ALTER TABLE landing_page_sections ALTER COLUMN body TYPE TEXT');
        }

        if (Schema::hasTable('landing_page_section_items')) {
            DB::statement('ALTER TABLE landing_page_section_items ALTER COLUMN body TYPE TEXT');
            DB::statement('ALTER TABLE landing_page_section_items ALTER COLUMN url TYPE TEXT');
        }

        if (Schema::hasTable('contact_submissions')) {
            DB::statement('ALTER TABLE contact_submissions ALTER COLUMN message TYPE TEXT');
        }
    }

    public function down(): void
    {
        //
    }
};
