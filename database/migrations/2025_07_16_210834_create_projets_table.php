<?php

use App\Enums\PhasesIdee;
use App\Enums\SousPhaseIdee;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('projets')) {
            Schema::create('projets', function (Blueprint $table) {
                $table->uuid('id')->primary();

                // Foreign keys
                $table->uuid('idee_projet_id');
                $table->uuid('secteur_id');
                $table->uuid('categorie_id');
                $table->uuid('responsable_id');
                $table->uuid('demandeur_id');
                $table->uuid('devise_id');

                // Unique identifiers
                $table->string('identifiant_bip')->nullable()->unique();
                $table->string('identifiant_sigfp')->nullable()->unique();

                // Status fields
                $table->boolean('est_coherent')->default(false);
                $table->enum('statut', StatutIdee::cases())->default(StatutIdee::BROUILLON);
                $table->enum('phase', PhasesIdee::cases())->default(PhasesIdee::identification);
                $table->enum('sous_phase', SousPhaseIdee::cases())->default(SousPhaseIdee::redaction);
                $table->json('decision')->nullable();

                // Basic info
                $table->string('intitule');
                $table->string('type_responsable');
                $table->string('demandeur_type');
                $table->enum('type_projet', TypesProjet::cases())->default(TypesProjet::simple);

                // Long text fields
                $table->longText('origine')->nullable();
                $table->longText('fondement')->nullable();
                $table->longText('situation_actuelle')->nullable();
                $table->longText('situation_desiree')->nullable();
                $table->longText('contraintes')->nullable();
                $table->longText('description_projet')->nullable();
                $table->longText('echeancier')->nullable();
                $table->longText('description_extrants')->nullable();
                $table->longText('caracteristiques')->nullable();

                $table->longText('impact_environnement')->nullable();
                $table->longText('aspect_organisationnel')->nullable();
                $table->longText('risques_immediats')->nullable();
                $table->longText('conclusions')->nullable();
                $table->longText('description')->nullable();
                $table->longText('description_decision')->nullable();
                $table->longText('estimation_couts')->nullable();
                $table->longText('public_cible')->nullable();
                $table->longText('constats_majeurs')->nullable();
                $table->longText('objectif_general')->nullable();
                $table->longText('sommaire')->nullable();

                // Decimal fields
                $table->decimal('score_climatique', 8, 2)->default(0.0);
                $table->decimal('score_amc', 8, 2)->default(0.0);
                $table->decimal('cout_dollar_americain', 15, 2)->nullable();
                $table->decimal('cout_euro', 15, 2)->nullable();
                $table->decimal('cout_dollar_canadien', 15, 2)->nullable();

                // Date fields
                $table->timestamp('date_debut_etude')->nullable();
                $table->timestamp('date_fin_etude')->nullable();

                // JSON fields
                $table->json('duree')->nullable();
                $table->json('cout_estimatif_projet')->nullable();
                $table->json('fiche_idee');
                $table->json('parties_prenantes')->nullable();
                $table->json('objectifs_specifiques')->nullable();
                $table->json('resultats_attendus')->nullable();

                // Boolean field
                $table->boolean('isdeleted')->default(false);

                $table->timestamps();
                $table->softDeletes();

                // Foreign key constraints
                $table->foreign('idees_projet_id')->references('id')->on('idees_projet')->onDelete('set null');
                $table->foreign('secteur_id')->references('id')->on('secteurs')->onDelete('cascade');
                $table->foreign('categorie_id')->references('id')->on('categories_projet')->onDelete('cascade');
                $table->foreign('responsable_id')->references('id')->on('utilisateurs')->onDelete('cascade');
                $table->foreign('demandeur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
                $table->foreign('devise_id')->references('id')->on('devises')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
