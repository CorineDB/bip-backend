<?php

namespace App\Listeners;

use App\Events\IdeeProjetTransformee;
use App\Models\Decision;
use App\Models\Projet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DupliquerIdeeProjetVersProjet implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    // PROPOSITION D'AMÉLIORATION:
    // Séparer en 2 phases:
    // 1. Synchrone: Créer/mettre à jour le projet principal
    // 2. Asynchrone: Dupliquer les relations via dispatch()
    // Cela évite les erreurs SQL sur le projet principal
    public function handle(IdeeProjetTransformee $event): void
    {
        try {
            DB::beginTransaction();

            $ideeProjet = $event->ideeProjet;

            // Chercher un projet existant basé sur l'idée de projet
            $projet = Projet::where('ideeProjetId', $ideeProjet->id)->first();

            // Préparer les données à dupliquer
            $projetData = $this->prepareProjetData($ideeProjet);

            if ($projet) {
                // Mettre à jour le projet existant
                $projet->update($projetData);
            } else {
                // Créer un nouveau projet
                $projetData['ideeProjetId'] = $ideeProjet->id;
                $projet = Projet::create($projetData);
            }

            // Dupliquer les relations
            $this->duplicateRelations($ideeProjet, $projet);

            DB::commit();

            Log::info("IdeeProjet {$ideeProjet->id} dupliquée vers Projet {$projet->id}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la duplication de IdeeProjet {$ideeProjet->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Préparer les données du projet à partir de l'idée de projet
     */
    private function prepareProjetData($ideeProjet): array
    {
        // Récupérer uniquement les attributs qui existent dans la table projets
        return [
            'identifiant_bip' => $ideeProjet->identifiant_bip,
            'identifiant_sigfp' => $ideeProjet->identifiant_sigfp,
            'ficheIdee' => $ideeProjet->ficheIdee,
            'statut' => $ideeProjet->statut,
            'phase' => $ideeProjet->phase,
            'sous_phase' => $ideeProjet->sous_phase,
            'decision' => $ideeProjet->decision,
            'sigle' => $ideeProjet->sigle,
            'type_projet' => $ideeProjet->type_projet,
            'parties_prenantes' => $ideeProjet->parties_prenantes,
            'objectifs_specifiques' => $ideeProjet->objectifs_specifiques,
            'resultats_attendus' => $ideeProjet->resultats_attendus,
            'isdeleted' => $ideeProjet->isdeleted,
            'body_projet' => $ideeProjet->body_projet,
            'cout_estimatif_projet' => $ideeProjet->cout_estimatif_projet,
            'cout_dollar_americain' => $ideeProjet->cout_dollar_americain,
            'cout_euro' => $ideeProjet->cout_euro,
            'date_debut_etude' => $ideeProjet->date_debut_etude,
            'date_fin_etude' => $ideeProjet->date_fin_etude,
            'date_prevue_demarrage' => $ideeProjet->date_prevue_demarrage,
            'date_effective_demarrage' => $ideeProjet->date_effective_demarrage,
            'cout_dollar_canadien' => $ideeProjet->cout_dollar_canadien,
            'risques_immediats' => $ideeProjet->risques_immediats,
            'sommaire' => $ideeProjet->sommaire,
            'objectif_general' => $ideeProjet->objectif_general,
            'conclusions' => $ideeProjet->conclusions,
            'description' => $ideeProjet->description,
            'constats_majeurs' => $ideeProjet->constats_majeurs,
            'public_cible' => $ideeProjet->public_cible,
            'estimation_couts' => $ideeProjet->estimation_couts,
            'description_decision' => $ideeProjet->description_decision,
            'impact_environnement' => $ideeProjet->impact_environnement,
            'aspect_organisationnel' => $ideeProjet->aspect_organisationnel,
            'description_extrants' => $ideeProjet->description_extrants,
            'caracteristiques' => $ideeProjet->caracteristiques,
            'score_climatique' => $ideeProjet->score_climatique,
            'score_amc' => $ideeProjet->score_amc,
            'duree' => $ideeProjet->duree,
            'description_projet' => $ideeProjet->description_projet,
            'origine' => $ideeProjet->origine,
            'situation_desiree' => $ideeProjet->situation_desiree,
            'situation_actuelle' => $ideeProjet->situation_actuelle,
            'contraintes' => $ideeProjet->contraintes,
            'echeancier' => $ideeProjet->echeancier,
            'fondement' => $ideeProjet->fondement,
            'secteurId' => $ideeProjet->secteurId,
            'ministereId' => $ideeProjet->ministereId,
            'categorieId' => $ideeProjet->categorieId,
            'responsableId' => $ideeProjet->responsableId,
            'demandeurId' => $ideeProjet->demandeurId ?: $ideeProjet->responsableId,
            // 'demandeur_type' => $ideeProjet->demandeur ? get_class($ideeProjet->demandeur) : 'App\\Models\\User',
            'titre_projet' => $ideeProjet->titre_projet,
        ];
    }

    /**
     * Dupliquer les relations de l'idée de projet vers le projet
     */
    private function duplicateRelations($ideeProjet, $projet): void
    {
        // Dupliquer les champs
        if ($ideeProjet->champs()->exists()) {
            $champsIds = $ideeProjet->champs()->pluck('champs.id');
            $pivotData = [];

            foreach ($ideeProjet->champs as $champ) {
                $pivotData[$champ->id] = [
                    'valeur' => $champ->pivot->valeur,
                    'commentaire' => $champ->pivot->commentaire,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $projet->champs()->sync($pivotData);
        }

        // Dupliquer les financements
        if ($ideeProjet->financements()->exists()) {
            $financementsIds = $ideeProjet->financements()->pluck('financements.id');
            $projet->financements()->sync($financementsIds);
        }

        // Dupliquer les cibles
        if ($ideeProjet->cibles()->exists()) {
            $ciblesIds = $ideeProjet->cibles()->pluck('cibles.id');
            $projet->cibles()->sync($ciblesIds);
        }

        // Dupliquer les ODDs
        if ($ideeProjet->odds()->exists()) {
            $oddsIds = $ideeProjet->odds()->pluck('odds.id');
            $projet->odds()->sync($oddsIds);
        }

        // Dupliquer les types d'intervention
        if ($ideeProjet->typesIntervention()->exists()) {
            $typesIds = $ideeProjet->typesIntervention()->pluck('types_intervention.id');
            $projet->typesIntervention()->sync($typesIds);
        }

        // Dupliquer les composants
        if ($ideeProjet->composants()->exists()) {
            $composantsIds = $ideeProjet->composants()->pluck('composants_programme.id');
            $projet->composants()->sync($composantsIds);
        }

        // Dupliquer les lieux d'intervention
        if ($ideeProjet->lieuxIntervention()->exists()) {
            // Supprimer les anciens lieux du projet
            $projet->lieuxIntervention()->delete();

            // Créer les nouveaux lieux
            foreach ($ideeProjet->lieuxIntervention as $lieu) {
                $projet->lieuxIntervention()->create([
                    'arrondissementId' => $lieu->arrondissementId,
                    'communeId' => $lieu->communeId,
                    'villageId' => $lieu->villageId,
                    'departementId' => $lieu->departementId,
                ]);
            }
        }

        // Dupliquer les decisions
        if ($ideeProjet->decisions()->exists()) {
            // Supprimer les anciennes decisions du projet
            $projet->decisions()->delete();

            // Créer les nouvelles decisions
            foreach ($ideeProjet->decisions as $decision) {
                // Ne dupliquer que les décisions qui ont une valeur non-nulle
                if ($decision->valeur !== null) {
                    Decision::create([
                        'valeur' => $decision->valeur,
                        'date' => $decision->date,
                        'observations' => $decision->observations,
                        'observateurId' => $decision->observateurId,
                        'objet_decision_id' => $projet->id,
                        'objet_decision_type' => get_class($projet),
                        'created_at' => $decision->created_at,
                        'updated_at' => $decision->updated_at
                    ]);
                }
            }
        }

        // Dupliquer les workflows
        if ($ideeProjet->workflows()->exists()) {
            // Supprimer les anciens workflows du projet
            $projet->workflows()->delete();

            // Créer les nouveaux workflows
            foreach ($ideeProjet->workflows as $workflow) {
                $projet->workflows()->create([
                    'statut' => $workflow->statut,
                    'phase' => $workflow->phase,
                    'sous_phase' => $workflow->sous_phase,
                    'date' => $workflow->date,
                    'created_at' => $workflow->created_at,
                    'updated_at' => $workflow->updated_at
                ]);
            }
        }

        // Dupliquer les commentaires
        if ($ideeProjet->commentaires()->exists()) {
            // Supprimer les anciens commentaires du projet
            $projet->commentaires()->delete();

            // Créer les nouveaux commentaires
            foreach ($ideeProjet->commentaires as $commentaire) {
                $projet->commentaires()->create([
                    'contenu' => $commentaire->contenu,
                    'userId' => $commentaire->userId,
                    'created_at' => $commentaire->created_at,
                    'updated_at' => $commentaire->updated_at
                ]);
            }
        }

        // Dupliquer les évaluations
        if ($ideeProjet->evaluations()->exists()) {
            // Supprimer les anciennes évaluations du projet
            $projet->evaluations()->delete();

            // Créer les nouvelles évaluations
            foreach ($ideeProjet->evaluations as $evaluation) {
                $nouvelleEvaluation = $projet->evaluations()->create([
                    'type_evaluation' => $evaluation->type_evaluation,
                    'date_debut_evaluation' => $evaluation->date_debut_evaluation,
                    'date_fin_evaluation' => $evaluation->date_fin_evaluation,
                    'evaluateur_id' => $evaluation->evaluateur_id,
                    'valider_par' => $evaluation->valider_par,

                    'valider_par' => $evaluation->valider_par,
                    'valider_le' => $evaluation->valider_le,
                    'commentaire' => $evaluation->commentaire,
                    'evaluation' => $evaluation->evaluation,
                    'resultats_evaluation' => $evaluation->resultats_evaluation,
                    'statut' => $evaluation->statut
                ]);

                // Dupliquer les critères d'évaluation
                foreach ($evaluation->evaluationCriteres as $critereEval) {
                    $nouvelleEvaluation->evaluationCriteres()->create([
                        'critere_id' => $critereEval->critere_id,
                        'evaluateur_id' => $critereEval->evaluateur_id,
                        'categorie_critere_id' => $critereEval->categorie_critere_id,
                        'notation_id' => $critereEval->notation_id,
                        'note' => $critereEval->note,
                        'commentaire' => $critereEval->commentaire,
                        'is_auto_evaluation' => $critereEval->is_auto_evaluation,
                        'est_archiver' => $critereEval->est_archiver
                    ]);
                }
            }
        }

        // Dupliquer les fichiers
        if ($ideeProjet->fichiers()->exists()) {
            // Supprimer les anciens fichiers du projet
            $projet->allFichiers()->delete();

            // Dupliquer les fichiers
            foreach ($ideeProjet->allFichiers as $fichier) {
                $projet->allFichiers()->create([
                    'nom_original' => $fichier->nom_original,
                    'nom_stockage' => $fichier->nom_stockage,
                    'chemin' => $fichier->chemin,
                    'extension' => $fichier->extension,
                    'mime_type' => $fichier->mime_type,
                    'taille' => $fichier->taille,
                    'hash_md5' => $fichier->hash_md5,
                    'description' => $fichier->description,
                    'metadata' => $fichier->metadata,
                    'categorie' => $fichier->categorie,
                    'ordre' => $fichier->ordre,
                    'uploaded_by' => $fichier->uploaded_by,
                    'is_public' => $fichier->is_public,
                    'is_active' => $fichier->is_active
                ]);
            }
        }
    }

    /**
     * Handle the event with improved async approach.
     */
    /*public function handleImproved(IdeeProjetTransformee $event): void
    {
        try {
            // Phase 1: Création/mise à jour synchrone du projet principal
            $ideeProjet = $event->ideeProjet;

            // Chercher un projet existant basé sur l'idée de projet
            $projet = Projet::where('ideeProjetId', $ideeProjet->id)->first();

            // Préparer les données à dupliquer
            $projetData = $this->prepareProjetData($ideeProjet);

            if ($projet) {
                // Mettre à jour le projet existant
                $projet->update($projetData);
            } else {
                // Créer un nouveau projet
                $projetData['ideeProjetId'] = $ideeProjet->id;
                $projet = Projet::create($projetData);
            }

            Log::info("Projet {$projet->id} créé/mis à jour depuis IdeeProjet {$ideeProjet->id}");

            // Phase 2: Duplication des relations en arrière-plan
            dispatch(function () use ($ideeProjet, $projet) {
                try {
                    DB::beginTransaction();
                    $this->duplicateRelations($ideeProjet, $projet);
                    DB::commit();
                    Log::info("Relations dupliquées pour Projet {$projet->id}");
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Erreur lors de la duplication des relations pour Projet {$projet->id}: " . $e->getMessage());
                }
            });

        } catch (\Exception $e) {
            Log::error("Erreur lors de la création du Projet depuis IdeeProjet {$ideeProjet->id}: " . $e->getMessage());
            throw $e;
        }
    }*/
}
