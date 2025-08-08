<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fichiers')) {
            Schema::create('fichiers', function (Blueprint $table) {
                $table->id();

                // Informations du fichier
                $table->string('nom_original');
                $table->string('nom_stockage');
                $table->string('chemin');
                $table->string('extension', 10);
                $table->string('mime_type');
                $table->unsignedBigInteger('taille'); // en octets
                $table->string('hash_md5')->nullable(); // Pour vérifier l'intégrité

                // Métadonnées optionnelles
                $table->string('description')->nullable();
                $table->json('metadata')->nullable(); // Données supplémentaires (dimensions image, durée vidéo, etc.)

                // Relation polymorphique
                $table->unsignedBigInteger('fichier_attachable_id');
                $table->string('fichier_attachable_type');
                $table->index(['fichier_attachable_id', 'fichier_attachable_type'], 'fichiers_attachable_index');

                // Catégorisation
                $table->string('categorie')->nullable(); // ex: 'document', 'image', 'rapport', etc.
                $table->integer('ordre')->default(0); // Ordre d'affichage

                // Audit et statut
                $table->unsignedBigInteger('uploaded_by');
                $table->boolean('is_public')->default(false);
                $table->boolean('is_active')->default(true);

                $table->timestamps();
                $table->softDeletes();

                // Foreign keys
                $table->foreign('uploaded_by')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichiers');
    }
};
