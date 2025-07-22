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
        if (Schema::hasTable('champs_projet')) {
            Schema::table('champs_projet', function (Blueprint $table) {
                $table->id();
                $table->jsonb('valeur')->nullable();
                $table->longText('commentaire')->nullable();
                $table->morphs('projetable');
                $table->bigInteger('champId')->unsigned();
                $table->foreign('champId')->references('id')->on('champs')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('champs_idee');
    }
};
