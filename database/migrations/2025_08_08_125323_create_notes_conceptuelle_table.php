<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notes_conceptuelle')) {
            Schema::create('notes_conceptuelle', function (Blueprint $table) {
                $table->id();

                // Foreign keys
                $table->unsignedBigInteger('valider_par')->index();
                $table->unsignedBigInteger('rediger_par')->index();
                $table->unsignedBigInteger('projetId')->index();

                $table->enum("statut", [-1, 0, 1]);

                // Basic info
                $table->string('intitule');

                // JSON fields
                $table->json('note_conceptuelle')->nullable();
                $table->json('decision')->nullable();

                $table->timestamps();
                $table->softDeletes();

                // Foreign key constraints
                $table->foreign('valider_par')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('rediger_par')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('projetId')->references('id')->on('projets')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notes_conceptuelle');
    }
};
