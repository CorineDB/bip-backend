<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commentaires')) {
            Schema::create('commentaires', function (Blueprint $table) {
                $table->id();
                $table->longText('commentaire');
                $table->timestamp('date')->nullable();
                $table->morphs('commentaireable');
                $table->bigInteger('commentateurId')->nullable()->unsigned();
                $table->foreign('commentateurId')->references('id')->on('users')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('commentaires');
    }
};
