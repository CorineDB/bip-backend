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
        Schema::create('tdrs', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('projet_id')->index()->constrained('projets')->onDelete('cascade');

            // Informations principales
            $table->enum('type', ['prefaisabilite', 'faisabilite']);
            $table->enum('statut', [
                'brouillon',
                'soumis',
                'en_evaluation',
                'retour_travail_supplementaire',
                'valide',
                'abandonne'
            ])->default('brouillon');
            $table->text('resume')->nullable();
            $table->json('termes_de_reference')->nullable();

            // Informations de soumission
            $table->timestamp('date_soumission')->nullable();
            $table->foreignId('soumis_par_id')->nullable()->index()->constrained('users')->onDelete('set null');
            $table->foreignId('rediger_par_id')->index()->constrained('users')->onDelete('set null');

            // Informations d'évaluation
            $table->timestamp('date_evaluation')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->foreignId('evaluateur_id')->nullable()->index()->constrained('users')->onDelete('set null');
            $table->foreignId('validateur_id')->nullable()->index()->constrained('users')->onDelete('set null');
            $table->json('evaluations_detaillees')->nullable();
            $table->text('commentaire_evaluation')->nullable();
            $table->text('commentaire_validation')->nullable();
            $table->enum('decision_validation', ['valider', 'reviser', 'abandonner'])->nullable();

            // Décision finale
            $table->enum('resultats_evaluation', [
                'passe',
                'retour_travail_supplementaire',
                'abandonne'
            ])->nullable();

            // Statistiques d'évaluation
            $table->integer('nombre_passe')->default(0);
            $table->integer('nombre_retour')->default(0);
            $table->integer('nombre_non_accepte')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index(['projet_id', 'type']);
            $table->index(['statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tdrs');
    }
};