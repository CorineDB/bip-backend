<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cibles_projets')) {
            Schema::create('cibles_projets', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('cibleId')->nullable()->unsigned();
                $table->foreign('cibleId')->references('id')->on('cibles')
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
        Schema::dropIfExists('cibles_projets');
    }
};
