<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('champs_sections')) {
            Schema::create('champs_sections', function (Blueprint $table) {
                $table->id();
                $table->text('intitule');
                $table->longText('description');
                $table->string('slug');
                $table->integer('ordre_affichage')->default(0);
                $table->enum('type', ['entete', 'formulaire', 'table_matiere', 'tableau'])->default('formulaire');

                $table->bigInteger('documentId')->unsigned();
                $table->foreign('documentId')->references('id')->on('documents')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');

                $table->unique(['intitule', 'documentId']);
                $table->unique(['slug', 'documentId']);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('champs_sections');
    }
};
