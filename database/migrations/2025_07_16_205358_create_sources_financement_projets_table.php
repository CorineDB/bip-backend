<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sources_financement_projets')) {
            Schema::create('sources_financement_projets', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('sourceId')->unsigned();
                $table->foreign('sourceId')->references('id')->on('financements')
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
        Schema::dropIfExists('sources_financement_projets');
    }
};
