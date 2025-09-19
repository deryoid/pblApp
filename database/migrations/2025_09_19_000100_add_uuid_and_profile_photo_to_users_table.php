<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'profile_photo_mime')) {
                $table->string('profile_photo_mime', 100)->nullable()->after('remember_token');
            }
        });

        // Add LONGBLOB via statement for broader image sizes
        if (!Schema::hasColumn('users', 'profile_photo')) {
            DB::statement("ALTER TABLE `users` ADD `profile_photo` LONGBLOB NULL AFTER `profile_photo_mime`");
        }

        // Backfill UUID values and make column unique + not null
        $users = DB::table('users')->whereNull('uuid')->orWhere('uuid', '')->get(['id', 'uuid']);
        foreach ($users as $u) {
            DB::table('users')->where('id', $u->id)->update(['uuid' => (string) Str::uuid()]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_photo')) {
                // Drop via raw to avoid platform differences
                DB::statement("ALTER TABLE `users` DROP COLUMN `profile_photo`");
            }
            if (Schema::hasColumn('users', 'profile_photo_mime')) {
                $table->dropColumn('profile_photo_mime');
            }
            if (Schema::hasColumn('users', 'uuid')) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            }
        });
    }
};

