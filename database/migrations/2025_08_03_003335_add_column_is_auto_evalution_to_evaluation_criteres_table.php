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
        if (Schema::hasTable('evaluation_criteres')) {
            Schema::table('evaluation_criteres', function (Blueprint $table) {
                if (!Schema::hasColumn('evaluation_criteres', 'is_auto_evaluation')) {
                    $table->boolean('is_auto_evaluation')->default(true);
                }
                if (!Schema::hasColumn('evaluation_criteres', 'est_archiver')) {
                    $table->boolean('est_archiver')->default(false);
                }
                if (Schema::hasColumn('evaluation_criteres', 'notation_id')) {
                    $table->unsignedBigInteger('notation_id')->nullable()->index()->change();
                }
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('evaluation_criteres')) {
            Schema::table('evaluation_criteres', function (Blueprint $table) {
                if (Schema::hasColumn('evaluation_criteres', 'is_auto_evaluation')) {
                    $table->dropColumn('is_auto_evaluation');
                }
                if (Schema::hasColumn('evaluation_criteres', 'est_archiver')) {
                    $table->dropColumn('est_archiver');
                }
                if (Schema::hasColumn('evaluation_criteres', 'notation_id')) {
                    //$table->dropForeign(['notation_id']);
                    $table->dropIndex(['notation_id']);
                    $table->bigInteger('notation_id')->nullable()->change();
                }
            });
        }
    }
};
