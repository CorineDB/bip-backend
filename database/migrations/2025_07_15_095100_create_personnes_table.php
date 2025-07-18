<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('personnes')) {
            Schema::create('personnes', function (Blueprint $table) {
                $table->id();
                $table->string('nom', 255);
                $table->string('prenom', 255);
                $table->string('poste', 255)->nullable();
                $table->bigInteger('organismeId')->unsigned();
                $table->foreign('organismeId')->references('id')->on('organisations')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('personnes');
    }
};
