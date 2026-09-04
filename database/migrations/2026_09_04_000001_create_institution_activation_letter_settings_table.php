<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_activation_letter_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('logo_position', 20)->default('left');
            $table->string('logo_path')->nullable();
            $table->string('signature_name')->nullable();
            $table->string('signature_title')->nullable();
            $table->text('signature_content')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('footer_logo_path')->nullable();
            $table->json('header_settings')->nullable();
            $table->json('footer_settings')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('institution_activation_letters')) {
            return;
        }

        $template = DB::table('institution_activation_letters')
            ->where(function ($query): void {
                $query->whereNotNull('header_settings')
                    ->orWhereNotNull('footer_settings')
                    ->orWhereNotNull('logo_path')
                    ->orWhereNotNull('footer_logo_path')
                    ->orWhereNotNull('signature_path');
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (! $template) {
            return;
        }

        DB::table('institution_activation_letter_settings')->insert([
            'key' => 'default',
            'updated_by' => null,
            'logo_position' => $template->logo_position ?: 'left',
            'logo_path' => $template->logo_path,
            'signature_name' => $template->signature_name,
            'signature_title' => $template->signature_title,
            'signature_content' => $template->signature_content,
            'signature_path' => $template->signature_path,
            'footer_logo_path' => $template->footer_logo_path,
            'header_settings' => $template->header_settings,
            'footer_settings' => $template->footer_settings,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_activation_letter_settings');
    }
};
