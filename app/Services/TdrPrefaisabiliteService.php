<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Models\Fichier;
use App\Models\Projet;
use App\Models\Decision;
use App\Models\Workflow;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use App\Http\Resources\projets\ProjetResource;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\Contracts\TdrPrefaisabiliteServiceInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TdrPrefaisabiliteService extends BaseService implements TdrPrefaisabiliteServiceInterface
{
    protected DocumentRepositoryInterface $documentRepository;
    protected ProjetRepositoryInterface $projetRepository;
    protected EvaluationRepositoryInterface $evaluationRepository;

    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        ProjetRepositoryInterface $projetRepository,
        EvaluationRepositoryInterface $evaluationRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->projetRepository = $projetRepository;
        $this->evaluationRepository = $evaluationRepository;
    }

    protected function getResourceClass(): string
    {
        return ProjetResource::class;
    }

    protected function getResourcesClass(): string
    {
        return ProjetResource::class;
    }

    /**
     * Soumettre les TDRs de préfaisabilité (SFD-010)
     */
    public function soumettreTdrs(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DPAF uniquement)
            if (!in_array(auth()->user()->type, ['dpaf', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette soumission.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::TDR_PREFAISABILITE->value, StatutIdee::R_TDR_PREFAISABILITE->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de soumission des TDRs de préfaisabilité.'
                ], 422);
            }

            // Traitement et sauvegarde du fichier TDR
            $fichierTdr = null;
            if (isset($data['fichier_tdr'])) {
                $fichierTdr = $this->sauvegarderFichierTdr($projet, $data['fichier_tdr'], $data['resume'] ?? '');
            }

            // Récupérer les commentaires des évaluations antérieures si c'est un retour
            $commentairesAnterieurs = $this->getCommentairesAnterieurs($projet);

            // Changer le statut du projet
            $projet->update([
                'statut' => StatutIdee::EVALUATION_TDR_PF,
                'phase' => $this->getPhaseFromStatut(StatutIdee::EVALUATION_TDR_PF),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::EVALUATION_TDR_PF)
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, StatutIdee::EVALUATION_TDR_PF);
            $this->enregistrerDecision(
                $projet,
                "Soumission des TDRs de préfaisabilité",
                $data['resume'] ?? 'TDRs soumis pour évaluation',
                auth()->id()
            );

            // Envoyer une notification
            $this->envoyerNotificationSoumission($projet, $fichierTdr);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'TDRs de préfaisabilité soumis avec succès.',
                'data' => [
                    'fichier_id' => $fichierTdr ? $fichierTdr->id : null,
                    'projet_id' => $projet->id,
                    'ancien_statut' => in_array($projet->statut->value, [StatutIdee::TDR_PREFAISABILITE->value, StatutIdee::R_TDR_PREFAISABILITE->value]) ? $projet->statut->value : StatutIdee::TDR_PREFAISABILITE->value,
                    'nouveau_statut' => StatutIdee::EVALUATION_TDR_PF->value,
                    'fichier_url' => $fichierTdr ? $fichierTdr->url : null,
                    'soumis_par' => auth()->id(),
                    'soumis_le' => now()->format('d/m/Y H:i:s'),
                    'commentaires_anterieurs' => $commentairesAnterieurs
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }


    /**
     * Apprécier et évaluer les TDRs de préfaisabilité (SFD-011)
     */
    public function evaluerTdrs(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!in_array(auth()->user()->type, ['dgpd', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette évaluation.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::EVALUATION_TDR_PF->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape d\'évaluation des TDRs.'
                ], 422);
            }

            // Vérifier qu'il y a des TDRs soumis
            $tdrsFichiers = $projet->fichiersParCategorie('tdr-prefaisabilite');
            if ($tdrsFichiers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun TDR trouvé pour ce projet.'
                ], 404);
            }

            // Créer ou mettre à jour l'évaluation
            $evaluation = $this->creerEvaluationTdr($projet, $data);

            // Calculer le résultat de l'évaluation selon les règles SFD-011
            $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, $data);

            // Traiter la décision selon le résultat
            $nouveauStatut = $this->traiterDecisionEvaluationTdr($projet, $resultatsEvaluation, $data);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, $nouveauStatut);
            $this->enregistrerDecision(
                $projet,
                "Évaluation des TDRs de préfaisabilité - " . ucfirst($resultatsEvaluation['resultat_global']),
                $data['commentaire'] ?? $resultatsEvaluation['message_resultat'],
                auth()->id()
            );

            // Envoyer une notification
            $this->envoyerNotificationEvaluation($projet, $resultatsEvaluation);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $this->getMessageSuccesEvaluation($resultatsEvaluation['resultat_global']),
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'projet_id' => $projet->id,
                    'resultat_global' => $resultatsEvaluation['resultat_global'],
                    'nouveau_statut' => $nouveauStatut->value,
                    'evaluateur' => auth()->id(),
                    'date_evaluation' => now()->format('d/m/Y H:i:s'),
                    'statistiques' => $resultatsEvaluation
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function soumettreRapportPrefaisabilite(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DPAF uniquement)
            if (!in_array(auth()->user()->type, ['dpaf', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette soumission.'
                ], 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::SOUMISSION_RAPPORT_PF->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de soumission du rapport de préfaisabilité.'
                ], 422);
            }

            // Traitement et sauvegarde du fichier rapport
            $fichierRapport = null;
            if (isset($data['fichier_rapport'])) {
                $fichierRapport = $this->sauvegarderFichierRapport($projet, $data['fichier_rapport'], $data);
            }

            // Enregistrer les informations du cabinet et recommandations
            $this->enregistrerInformationsCabinet($projet, $data);

            // Changer le statut du projet
            $projet->update([
                'statut' => StatutIdee::VALIDATION_PF,
                'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_PF),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_PF)
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, StatutIdee::VALIDATION_PF);
            $this->enregistrerDecision(
                $projet,
                "Soumission du rapport de préfaisabilité",
                "Rapport soumis par cabinet: " . ($data['nom_cabinet'] ?? 'N/A'),
                auth()->id()
            );

            // Envoyer une notification
            $this->envoyerNotificationSoumissionRapport($projet, $fichierRapport, $data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rapport de préfaisabilité soumis avec succès.',
                'data' => [
                    'fichier_id' => $fichierRapport ? $fichierRapport->id : null,
                    'projet_id' => $projet->id,
                    'ancien_statut' => StatutIdee::SOUMISSION_RAPPORT_PF->value,
                    'nouveau_statut' => StatutIdee::VALIDATION_PF->value,
                    'cabinet' => [
                        'nom' => $data['nom_cabinet'] ?? null,
                        'contact' => $data['contact_cabinet'] ?? null,
                        'email' => $data['email_cabinet'] ?? null
                    ],
                    'recommandation' => $data['recommandation_adaptation'] ?? null,
                    'fichier_url' => $fichierRapport ? $fichierRapport->url : null,
                    'soumis_par' => auth()->id(),
                    'soumis_le' => now()->format('d/m/Y H:i:s')
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Sauvegarder le fichier TDR téléversé
     */
    private function sauvegarderFichierTdr(Projet $projet, $fichier, string $resume): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = 'tdr_prefaisabilite_' . $projet->id . '_' . time() . '.' . $extension;
        $chemin = $fichier->storeAs('tdrs/prefaisabilite', $nomStockage, 'public');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => $resume ?: 'Termes de référence pour l\'étude de préfaisabilité',
            'metadata' => [
                'type_document' => 'tdr-prefaisabilite',
                'projet_id' => $projet->id,
                'soumis_par' => auth()->id(),
                'soumis_le' => now()
            ],
            'fichier_attachable_id' => $projet->id,
            'fichier_attachable_type' => Projet::class,
            'categorie' => 'tdr-prefaisabilite',
            'ordre' => 1,
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }

    /**
     * Récupérer les commentaires des évaluations antérieures
     */
    private function getCommentairesAnterieurs(Projet $projet): ?string
    {
        if ($projet->statut->value === StatutIdee::R_TDR_PREFAISABILITE->value) {
            $derniereEvaluation = $projet->evaluations()
                ->where('type_evaluation', 'tdr-prefaisabilite')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            return $derniereEvaluation ? $derniereEvaluation->commentaire : null;
        }
        return null;
    }

    /**
     * Créer une évaluation TDR
     */
    private function creerEvaluationTdr(Projet $projet, array $data)
    {
        // Récupérer une évaluation en cours existante ou en créer une nouvelle
        $evaluation = $projet->evaluations()
            ->where('type_evaluation', 'tdr-prefaisabilite')
            ->where('statut', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$evaluation) {
            // Récupérer l'évaluation parent si c'est une ré-évaluation
            $evaluationParent = $projet->evaluations()
                ->where('type_evaluation', 'tdr-prefaisabilite')
                ->where('statut', 1)
                ->orderBy('created_at', 'desc')
                ->first();
            // Récupérer le canevas d'appréciation des TDRs
            $canevasAppreciation = $this->documentRepository->getModel()
                ->where('type', 'formulaire')
                ->where('slug', 'canevas-appreciation-tdr')
                ->orderBy('created_at', 'desc')
                ->first();

            // Créer la grille d'évaluation basée sur le canevas
            $grilleEvaluation = [];
            if ($canevasAppreciation) {
                $champsEvaluation = $canevasAppreciation->all_champs;
                foreach ($champsEvaluation as $champ) {
                    $grilleEvaluation[] = [
                        'champ_id' => $champ->id,
                        'label' => $champ->label,
                        'attribut' => $champ->attribut,
                        'is_required' => $champ->is_required,
                        'description' => $champ->info ?? "Évaluation de: {$champ->label}",
                        'statut_evaluation' => null, // passe, retour, non_accepte
                        'commentaire_evaluateur' => null,
                        'options_notation' => $canevasAppreciation->evaluation_configs['options_notation'] ?? []
                    ];
                }
            }
            $evaluation = $projet->evaluations()->create([
                'type_evaluation' => 'tdr-prefaisabilite',
                'evaluateur_id' => auth()->id(),
                'evaluation' => [],
                'evaluation' => $grilleEvaluation,
                'resultats_evaluation' => [],
                'date_debut_evaluation' => now(),
                'statut' => 0, // En cours
                'id_evaluation' => $evaluationParent ? $evaluationParent->id : null
            ]);
        }


        // Enregistrer les appréciations pour chaque champ
        if (isset($data['evaluations_champs'])) {
            $grilleActuelle = $evaluation->evaluation ?? [];
            $nouvelleGrille = [];
            foreach ($grilleActuelle as $critere) {
                $champId = $critere['champ_id'];
                // Chercher l'évaluation correspondante dans les données reçues
                $evaluationChamp = collect($data['evaluations_champs'])->firstWhere('champ_id', $champId);
                if ($evaluationChamp) {
                    $critere['statut_evaluation'] = $evaluationChamp['appreciation'];
                    $critere['commentaire_evaluateur'] = $evaluationChamp['commentaire'] ?? null;
                }
                $nouvelleGrille[] = $critere;
            }
            $evaluation->update(['evaluation' => $nouvelleGrille]);
        }

        // Finaliser l'évaluation si demandé
        if (isset($data['finaliser']) && $data['finaliser']) {
            $evaluation->update([
                'date_fin_evaluation' => now(),
                'statut' => 1,
                'commentaire' => $data['commentaire'] ?? null
            ]);
        }

        return $evaluation;
    }

    /**
     * Calculer le résultat d'évaluation selon les règles SFD-011
     */
    private function calculerResultatEvaluationTdr($evaluation, array $data): array
    {
        $evaluationsChamps = $data['evaluations_champs'] ?? [];

        $nombrePasse = 0;
        $nombreRetour = 0;
        $nombreNonAccepte = 0;
        $nombreNonEvalues = 0;
        $totalChamps = count($evaluationsChamps);

        // Compter les appréciations
        foreach ($evaluationsChamps as $evalChamp) {
            $appreciation = $evalChamp['appreciation'] ?? null;

            switch ($appreciation) {
                case 'passe':
                    $nombrePasse++;
                    break;
                case 'retour':
                    $nombreRetour++;
                    break;
                case 'non_accepte':
                    $nombreNonAccepte++;
                    break;
                default:
                    $nombreNonEvalues++;
                    break;
            }
        }

        // Appliquer les règles métier de SFD-011
        $resultat = $this->determinerResultatSelonRegles([
            'passe' => $nombrePasse,
            'retour' => $nombreRetour,
            'non_accepte' => $nombreNonAccepte,
            'non_evalues' => $nombreNonEvalues,
            'total' => $totalChamps
        ]);

        return array_merge($resultat, [
            'nombre_passe' => $nombrePasse,
            'nombre_retour' => $nombreRetour,
            'nombre_non_accepte' => $nombreNonAccepte,
            'nombre_non_evalues' => $nombreNonEvalues,
            'total_champs' => $totalChamps
        ]);
    }

    /**
     * Déterminer le résultat selon les règles SFD-011
     */
    private function determinerResultatSelonRegles(array $compteurs): array
    {
        // Règle 1: Si des questions n'ont pas été complétées
        if ($compteurs['non_evalues'] > 0) {
            return [
                'resultat_global' => 'non_accepte',
                'message_resultat' => 'Non accepté - Des questions n\'ont pas été complétées',
                'raison' => 'Questions non complétées'
            ];
        }

        // Règle 2: Si une réponse a été évaluée comme "Non accepté"
        if ($compteurs['non_accepte'] > 0) {
            return [
                'resultat_global' => 'non_accepte',
                'message_resultat' => 'Non accepté - Une ou plusieurs réponses évaluées comme "Non accepté"',
                'raison' => 'Réponses non acceptées'
            ];
        }

        // Règle 3: Si 10 ou plus des réponses ont été évaluées comme "Retour"
        if ($compteurs['retour'] >= 10) {
            return [
                'resultat_global' => 'non_accepte',
                'message_resultat' => 'Non accepté - Trop de retours (10 ou plus)',
                'raison' => 'Seuil de retours dépassé'
            ];
        }

        // Si toutes les réponses sont "Passe"
        if ($compteurs['passe'] === $compteurs['total'] && $compteurs['retour'] === 0) {
            return [
                'resultat_global' => 'passe',
                'message_resultat' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                'raison' => 'Toutes les questions approuvées'
            ];
        }

        // Sinon: Retour pour travail supplémentaire
        return [
            'resultat_global' => 'retour',
            'message_resultat' => 'Retour pour un travail supplémentaire (Contient des "Retours" mais pas suffisamment pour qu\'il ne soit pas accepté)',
            'raison' => 'Améliorations nécessaires'
        ];
    }

    /**
     * Traiter la décision d'évaluation selon le résultat
     */
    private function traiterDecisionEvaluationTdr(Projet $projet, array $resultats, array $data): StatutIdee
    {
        switch ($resultats['resultat_global']) {
            case 'passe':
                // La présélection a été un succès → SoumissionRapportPF
                $projet->update([
                    'statut' => StatutIdee::SOUMISSION_RAPPORT_PF,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_PF),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_PF)
                ]);
                return StatutIdee::SOUMISSION_RAPPORT_PF;

            case 'retour':
                // Retour pour travail supplémentaire → 04a_R:TDR_Préfaisabilité
                $projet->update([
                    'statut' => StatutIdee::R_TDR_PREFAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::R_TDR_PREFAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_TDR_PREFAISABILITE)
                ]);
                return StatutIdee::R_TDR_PREFAISABILITE;

            case 'non_accepte':
            default:
                // Non accepté → 04a:TDR_Préfaisabilité ou Abandon selon l'action
                if (isset($data['action']) && $data['action'] === 'abandonner') {
                    $projet->update([
                        'statut' => StatutIdee::ABANDON,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::ABANDON),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::ABANDON)
                    ]);
                    return StatutIdee::ABANDON;
                } else {
                    $projet->update([
                        'statut' => StatutIdee::TDR_PREFAISABILITE,
                        'phase' => $this->getPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                        'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE)
                    ]);
                    return StatutIdee::TDR_PREFAISABILITE;
                }
        }
    }

    /**
     * Sauvegarder le fichier rapport de préfaisabilité
     */
    private function sauvegarderFichierRapport(Projet $projet, $fichier, array $data): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = 'rapport_prefaisabilite_' . $projet->id . '_' . time() . '.' . $extension;
        $chemin = $fichier->storeAs('rapports/prefaisabilite', $nomStockage, 'public');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => 'Rapport d\'étude de préfaisabilité - Cabinet: ' . ($data['nom_cabinet'] ?? 'N/A'),
            'metadata' => [
                'type_document' => 'rapport-prefaisabilite',
                'projet_id' => $projet->id,
                'cabinet' => [
                    'nom' => $data['nom_cabinet'] ?? null,
                    'contact' => $data['contact_cabinet'] ?? null,
                    'email' => $data['email_cabinet'] ?? null,
                    'adresse' => $data['adresse_cabinet'] ?? null
                ],
                'recommandation_adaptation' => $data['recommandation_adaptation'] ?? null,
                'soumis_par' => auth()->id(),
                'soumis_le' => now()
            ],
            'fichier_attachable_id' => $projet->id,
            'fichier_attachable_type' => Projet::class,
            'categorie' => 'rapport-prefaisabilite',
            'ordre' => 1,
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }

    /**
     * Enregistrer les informations du cabinet dans les métadonnées du projet
     */
    private function enregistrerInformationsCabinet(Projet $projet, array $data): void
    {
        // Récupérer les métadonnées existantes ou créer un nouveau tableau
        $metadata = $projet->metadata ?? [];

        // Ajouter les informations de préfaisabilité
        $metadata['prefaisabilite'] = [
            'cabinet' => [
                'nom' => $data['nom_cabinet'] ?? null,
                'contact' => $data['contact_cabinet'] ?? null,
                'email' => $data['email_cabinet'] ?? null,
                'adresse' => $data['adresse_cabinet'] ?? null,
                'telephone' => $data['telephone_cabinet'] ?? null
            ],
            'recommandation_adaptation' => $data['recommandation_adaptation'] ?? null,
            'date_soumission_rapport' => now(),
            'soumis_par' => auth()->id()
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);
    }

    // Méthodes utilitaires (à implémenter selon les besoins)
    private function envoyerNotificationSoumission($projet, $fichier)
    { /* À implémenter */
    }
    private function envoyerNotificationEvaluation($projet, array $resultats)
    { /* À implémenter */
    }
    private function envoyerNotificationSoumissionRapport($projet, $fichier, array $data)
    { /* À implémenter */
    }

    private function getMessageSuccesEvaluation(string $resultat): string
    {
        return match ($resultat) {
            'passe' => 'TDRs approuvés avec succès. Projet peut passer à la soumission du rapport.',
            'retour' => 'TDRs nécessitent des améliorations.',
            'non_accepte' => 'TDRs non acceptés.',
            default => 'Évaluation effectuée avec succès.'
        };
    }

    // Méthodes utilitaires du workflow (réutilisées)
    private function enregistrerWorkflow($projet, $nouveauStatut)
    {
        Workflow::create([
            'statut' => $nouveauStatut,
            'phase' => $this->getPhaseFromStatut($nouveauStatut),
            'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
            'date' => now(),
            'projetable_id' => $projet->id,
            'projetable_type' => get_class($projet),
        ]);
    }

    private function enregistrerDecision($projet, $valeur, $observations, $observateurId)
    {
        Decision::create([
            'valeur' => $valeur,
            'date' => now(),
            'observations' => $observations,
            'observateurId' => $observateurId,
            'objet_decision_id' => $projet->id,
            'objet_decision_type' => get_class($projet),
        ]);
    }

    private function getPhaseFromStatut($statut)
    {
        return match ($statut) {
            StatutIdee::TDR_PREFAISABILITE => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::R_TDR_PREFAISABILITE => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::EVALUATION_TDR_PF => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::SOUMISSION_RAPPORT_PF => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::VALIDATION_PF => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::ABANDON => \App\Enums\PhasesIdee::evaluation_ex_tante,
            default => \App\Enums\PhasesIdee::evaluation_ex_tante,
        };
    }

    private function getSousPhaseFromStatut($statut)
    {
        return match ($statut) {
            StatutIdee::TDR_PREFAISABILITE => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
            StatutIdee::R_TDR_PREFAISABILITE => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
            StatutIdee::EVALUATION_TDR_PF => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
            StatutIdee::SOUMISSION_RAPPORT_PF => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
            StatutIdee::VALIDATION_PF => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
            StatutIdee::ABANDON => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
            default => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
        };
    }
}
