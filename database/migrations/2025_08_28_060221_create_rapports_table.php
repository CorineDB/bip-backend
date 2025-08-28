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
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('projet_id')->index()->constrained('projets')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->index()->constrained('rapports')->onDelete('cascade');

            // Type de rapport
            $table->enum('type', ['prefaisabilite', 'faisabilite', 'evaluation_ex_ante']);

            // Statut du rapport
            $table->enum('statut', [
                'brouillon',
                'soumis',
                'valide',
                'rejete'
            ])->default('brouillon');

            // Informations principales
            $table->string('intitule');

            // Données de la checklist de suivi (JSON)
            $table->json('checklist_suivi')->nullable();

            // Informations du cabinet d'étude
            $table->json('info_cabinet_etude')->nullable();

            // Recommandations
            $table->text('recommandation')->nullable();

            // Informations de soumission
            $table->timestamp('date_soumission')->nullable();
            $table->foreignId('soumis_par_id')->nullable()->index()->constrained('users')->onDelete('set null');

            // Informations de validation
            $table->timestamp('date_validation')->nullable();
            $table->foreignId('validateur_id')->nullable()->index()->constrained('users')->onDelete('set null');

            // Commentaires
            $table->text('commentaire_validation')->nullable();

            // Décisions
            $table->json('decision')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index(['projet_id', 'type']);
            $table->index(['statut']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};