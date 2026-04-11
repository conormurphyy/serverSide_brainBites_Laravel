<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 60)->nullable()->unique()->after('name');
            $table->text('bio')->nullable()->after('profile_photo_path');
        });

        $used = [];

        DB::table('users')
            ->orderBy('id')
            ->select('id', 'name', 'email')
            ->get()
            ->each(function ($user) use (&$used): void {
                $seed = Str::slug((string) ($user->name ?: ''), '_');

                if ($seed === '') {
                    $seed = Str::before((string) ($user->email ?: ''), '@');
                }

                $seed = Str::slug($seed, '_');
                if ($seed === '') {
                    $seed = 'user';
                }

                $candidate = Str::limit($seed, 56, '');
                if ($candidate === '') {
                    $candidate = 'user';
                }

                $counter = 2;
                while (in_array($candidate, $used, true) || DB::table('users')->where('username', $candidate)->exists()) {
                    $suffix = '_'.$counter;
                    $base = Str::limit($seed, max(1, 60 - strlen($suffix)), '');
                    $candidate = $base.$suffix;
                    $counter++;
                }

                $used[] = $candidate;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $candidate]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_username_unique');
            $table->dropColumn(['username', 'bio']);
        });
    }
};
