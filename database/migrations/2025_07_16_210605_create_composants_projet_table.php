<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('composants_projet')) {
            Schema::create('composants_projet', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('composantId')->nullable()->unsigned();
                $table->foreign('composantId')->references('id')->on('composants')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->morphs('projetable');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('composants_projets');
    }
};
