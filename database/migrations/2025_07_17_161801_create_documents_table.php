<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->text('nom')->unique();
                $table->string('slug')->unique()->index();
                $table->longText('description')->nullable();
                $table->bigInteger('categorieId')->nullable()->unsigned();
                $table->foreign('categorieId')->references('id')->on('categories_document')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->enum('type', ['document', 'formulaire', 'grille', 'checklist'])->default('formulaire');
                $table->jsonb('metadata')->nullable();
                $table->jsonb('structure')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
