<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('odds_projets')) {
            Schema::create('odds_projets', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('oddId')->nullable()->unsigned();
                $table->foreign('oddId')->references('id')->on('odds')
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
        Schema::dropIfExists('odds_projets');
    }
};
