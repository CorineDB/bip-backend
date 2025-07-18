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
        if (!Schema::hasTable('idees_projet')) {
            Schema::create('idees_projet', function (Blueprint $table) {
                $table->id();

                // Foreign keys
                $table->bigInteger('secteurId')->unsigned();
                $table->bigInteger('ministereId')->unsigned();
                $table->bigInteger('categorieId')->unsigned();
                $table->bigInteger('responsableId')->unsigned();
                $table->bigInteger('demandeurId')->unsigned();

                // Unique identifiers
                $table->string('identifiant_bip')->nullable()->unique();
                $table->string('identifiant_sigfp')->nullable()->unique();

                // Status fields
                $table->boolean('est_coherent')->default(false);
                $table->enum('statut', StatutIdee::values())->default(StatutIdee::BROUILLON->value);
                $table->enum('phase', PhasesIdee::values())->default(PhasesIdee::identification->value);
                $table->enum('sous_phase', SousPhaseIdee::values())->default(SousPhaseIdee::redaction->value);
                $table->json('decision')->nullable();

                // Basic info
                $table->string('titre_projet');
                $table->string('sigle');
                $table->enum('type_projet', TypesProjet::values())->default(TypesProjet::simple->value);
                $table->string('duree')->nullable();
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
                $table->timestamp('date_prevue_demarrage')->nullable();
                $table->timestamp('date_effective_demarrage')->nullable();


                // JSON fields
                $table->json('cout_estimatif_projet')->nullable();
                $table->json('ficheIdee');
                $table->json('parties_prenantes')->nullable();
                $table->json('objectifs_specifiques')->nullable();
                $table->json('resultats_attendus')->nullable();
                $table->json('body_projet');

                // Boolean field
                $table->boolean('isdeleted')->default(false);

                $table->timestamps();
                $table->softDeletes();

                // Foreign key constraints
                $table->foreign('ministereId')->references('id')->on('organisations')->onDelete('set null');
                $table->foreign('secteurId')->references('id')->on('secteurs')->onDelete('cascade');
                $table->foreign('categorieId')->references('id')->on('categories_projet')->onDelete('cascade');
                $table->foreign('responsableId')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('demandeurId')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('idees_projet');
    }
};
