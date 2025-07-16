<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lieux_intervention_projets')) {
            Schema::create('lieux_intervention_projets', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('departementId')->unsigned();
                $table->foreign('departementId')->references('id')->on('departements')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->bigInteger('communeId')->unsigned();
                $table->foreign('communeId')->references('id')->on('communes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->bigInteger('arrondissementId')->nullable()->unsigned();
                $table->foreign('arrondissementId')->references('id')->on('arrondissements')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->bigInteger('villageId')->nullable()->unsigned();
                $table->foreign('villageId')->references('id')->on('villages')
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
        Schema::dropIfExists('lieux_intervention_projets');
    }
};
