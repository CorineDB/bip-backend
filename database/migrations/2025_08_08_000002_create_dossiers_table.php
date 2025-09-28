<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('dossiers')) {

            Schema::create('dossiers', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('dossiers')->onDelete('cascade');
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->text('path')->nullable(); // chemin complet /dossier1/sousdossier
                $table->boolean('is_public')->default(false);
                $table->string('couleur', 7)->nullable(); // couleur hexadécimale #ffffff
                $table->string('icone')->nullable(); // nom de l'icône
                $table->integer('profondeur')->default(0); // niveau de profondeur
                $table->timestamps();
                $table->softDeletes();

                // Index pour optimiser les requêtes hiérarchiques
                $table->index(['parent_id', 'created_by']);
                $table->index('profondeur');
                $table->index('path');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('dossiers');
    }
};
