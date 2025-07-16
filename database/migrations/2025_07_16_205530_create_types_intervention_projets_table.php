<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('types_intervention_projets')) {
            Schema::create('types_intervention_projets', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('typeId')->unsigned();
                $table->foreign('typeId')->references('id')->on('types_intervention')
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
        Schema::dropIfExists('types_intervention_projets');
    }
};
