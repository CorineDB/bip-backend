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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'settings')) {
                    $table->json('settings')->nullable();
                }
                if (!Schema::hasColumn('users', 'person')) {
                    $table->json('person')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'settings')) {
                    $table->dropColumn('settings');
                }
                if (Schema::hasColumn('users', 'person')) {
                    $table->dropColumn('person');
                }
            });
        }
    }
};
