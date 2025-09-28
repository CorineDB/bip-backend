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
        Schema::table('rapports', function (Blueprint $table) {
            // Checklists pour préfaisabilité
            $table->json('checklist_suivi_rapport_prefaisabilite')->nullable();
            $table->json('checklists_mesures_adaptation_haut_risque')->nullable();

            // Checklists pour faisabilité
            $table->json('checklist_etude_faisabilite_marche')->nullable();
            $table->json('checklist_etude_faisabilite_economique')->nullable();
            $table->json('checklist_etude_faisabilite_technique')->nullable();
            $table->json('checklist_etude_faisabilite_organisationnelle_et_juridique')->nullable();
            $table->json('checklist_suivi_analyse_faisabilite_financiere')->nullable();
            $table->json('checklist_suivi_etude_analyse_impact_environnementale_et_sociale')->nullable();
            $table->json('checklist_suivi_assurance_qualite_rapport_etude_faisabilite')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            $table->dropColumn([
                'checklist_suivi_rapport_prefaisabilite',
                'checklists_mesures_adaptation_haut_risque',
                'checklist_etude_faisabilite_marche',
                'checklist_etude_faisabilite_economique',
                'checklist_etude_faisabilite_technique',
                'checklist_etude_faisabilite_organisationnelle_et_juridique',
                'checklist_suivi_analyse_faisabilite_financiere',
                'checklist_suivi_etude_analyse_impact_environnementale_et_sociale',
                'checklist_suivi_assurance_qualite_rapport_etude_faisabilite'
            ]);
        });
    }
};
