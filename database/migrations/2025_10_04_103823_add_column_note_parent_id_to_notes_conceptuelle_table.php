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
        if (Schema::hasTable('notes_conceptuelle')) {
            Schema::table('notes_conceptuelle', function (Blueprint $table) {
                $table->bigInteger('parentId')->nullable()->unsigned()->index();
                $table->foreign('parentId')->references('id')->on('notes_conceptuelle')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes_conceptuelle', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
