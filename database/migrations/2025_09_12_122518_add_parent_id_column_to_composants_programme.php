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
        if (Schema::hasTable('composants_programme')) {
            Schema::table('composants_programme', function (Blueprint $table) {
                $table->bigInteger('parentId')->nullable()->unsigned()->index();
                $table->foreign('parentId')->references('id')->on('composants_programme')
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
        Schema::table('composants_programme', function (Blueprint $table) {
            //
        });
    }
};
