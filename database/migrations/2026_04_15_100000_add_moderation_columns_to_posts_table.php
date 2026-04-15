<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('approval_status')->default('approved')->after('is_public');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('review_notes')->nullable()->after('rejected_at');

            $table->index(['approval_status', 'published_at']);
        });

        DB::table('posts')
            ->where('is_public', true)
            ->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);

        DB::table('posts')
            ->where('is_public', false)
            ->update([
                'approval_status' => 'draft',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['approval_status', 'published_at']);
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejected_at',
                'review_notes',
            ]);
        });
    }
};
