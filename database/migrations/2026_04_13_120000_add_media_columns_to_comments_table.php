<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('body');
            $table->string('voice_note_path')->nullable()->after('image_path');
            $table->decimal('voice_note_duration', 4, 1)->nullable()->after('voice_note_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropColumn(['image_path', 'voice_note_path', 'voice_note_duration']);
        });
    }
};
