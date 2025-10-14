<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Repositories\Contracts\TdrRepositoryInterface;
use App\Models\Fichier;
use App\Models\Projet;
use App\Models\Rapport;
use App\Models\Decision;
use App\Models\Workflow;
use App\Models\Dossier;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
use App\Http\Resources\FichierResource;
use App\Http\Resources\projets\ProjetResource;
use App\Http\Resources\TdrResource;
use App\Http\Resources\RapportResource;
use App\Http\Resources\UserResource;
use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\Tdr;
use App\Services\Contracts\CategorieCritereServiceInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\Contracts\TdrPrefaisabiliteServiceInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Helpers\SlugHelper;
use App\Http\Resources\CanevasAppreciationTdrResource;
use App\Http\Resources\projets\integration\ProjetsResource;

class TdrPrefaisabiliteService extends BaseService implements TdrPrefaisabiliteServiceInterface
{
    protected DocumentRepositoryInterface $documentRepository;
    protected ProjetRepositoryInterface $projetRepository;
    protected EvaluationRepositoryInterface $evaluationRepository;
    protected CategorieCritereServiceInterface $categorieCritereService;
    protected TdrRepositoryInterface $tdrRepository;

    public function __construct(
        DocumentRepositoryInterface $documentRepository,
        ProjetRepositoryInterface $projetRepository,
        EvaluationRepositoryInterface $evaluationRepository,
        CategorieCritereServiceInterface $categorieCritereService,
        TdrRepositoryInterface $tdrRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->projetRepository = $projetRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->categorieCritereService = $categorieCritereService;
        $this->tdrRepository = $tdrRepository;
    }

    protected function getResourceClass(): string
    {
        return TdrResource::class;
    }

    protected function getResourcesClass(): string
    {
        return TdrResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Extraire les données spécifiques au payload
            $champsData = $data['champs'] ?? [];
            $termesDeReference = $data['termes_de_reference'] ?? [];
            $documentsData = $data['documents'] ?? [];
            $estSoumis = $data['est_soumis'] ?? false;
            $projetId = $data['projet_id'] ?? null;
            $resume = $data['resume'] ?? null;

            if (!$projetId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID du projet requis.'
                ], 422);
            }

            // Déterminer le statut selon est_soumis
            $statut = $estSoumis ? 'soumis' : 'brouillon';

            // Préparer les données du TDR
            $tdrData = [
                'type' => 'prefaisabilite',
                'statut' => $statut,
                'resume' => $resume,
                'termes_de_reference' => $termesDeReference,
                'rediger_par_id' => auth()->id(),
                'soumis_par_id' => $estSoumis ? auth()->id() : null,
                'date_soumission' => $estSoumis ? now() : null,
            ];

            // Chercher ou créer un TDR unique par projet et type
            $tdrPrefaisabilite = $this->tdrRepository->findByProjetAndType($projetId, 'prefaisabilite');

            if ($tdrPrefaisabilite) {
                // Mettre à jour le TDR existant
                $this->tdrRepository->update($tdrPrefaisabilite->id, $tdrData);
                $tdrPrefaisabilite->refresh();
                $message = 'TDR de préfaisabilité mis à jour avec succès.';
                $statusCode = 200;
            } else {
                // Créer un nouveau TDR
                $tdrData['projet_id'] = $projetId;
                $tdrPrefaisabilite = $this->tdrRepository->create($tdrData);
                $message = 'TDR de préfaisabilité créé avec succès.';
                $statusCode = 201;
            }

            // Récupérer le canevas de rédaction TDR préfaisabilité
            $canevasTdr = $this->documentRepository->getModel()->where([
                'type' => 'formulaire'
            ])->whereHas('categorie', function ($query) {
                $query->where('slug', 'canevas-tdr-prefaisabilite');
            })->orderBy('created_at', 'desc')->first();

            if ($canevasTdr && !empty($champsData)) {
                // Sauvegarder les champs dynamiques basés sur le canevas
                $this->saveDynamicFieldsFromCanevas($tdrPrefaisabilite, $champsData, $canevasTdr);
            }

            // Gérer les documents/fichiers
            if (!empty($documentsData)) {
                $this->handleDocuments($tdrPrefaisabilite, $documentsData);
            }

            // Recharger le TDR avec ses relations pour obtenir les champs
            $tdrPrefaisabilite = $this->tdrRepository->findById(
                $tdrPrefaisabilite->id,
                ['*'],
                ['champs', 'fichiers', 'projet']
            );

            // Mettre à jour les termes de référence avec les données des champs
            if ($tdrPrefaisabilite->champs) {
                $termesDeReferenceFormates = $tdrPrefaisabilite->champs->map(function ($champ) {
                    return [
                        'id' => $champ->id,
                        'label' => $champ->label,
                        'attribut' => $champ->attribut,
                        'ordre_affichage' => $champ->ordre_affichage,
                        'type_champ' => $champ->type_champ,
                        'valeur' => $champ->pivot->valeur,
                        'commentaire' => $champ->pivot->commentaire,
                        'updated_at' => $champ->pivot->updated_at
                    ];
                });

                $this->tdrRepository->update($tdrPrefaisabilite->id, [
                    'termes_de_reference' => $termesDeReferenceFormates->toArray()
                ]);
            }

            // Mettre à jour le statut du projet si TDR soumis
            if ($estSoumis) {
                $projet = $tdrPrefaisabilite->projet;
                $projet->fill([
                    'statut' => StatutIdee::TDR_PREFAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                    'resume_tdr_prefaisabilite' => $resume
                ]);
                $projet->save();
                $projet->refresh();
            }

            DB::commit();

            // Recharger le TDR final avec toutes ses relations
            $tdrFinal = $this->tdrRepository->findById(
                $tdrPrefaisabilite->id,
                ['*'],
                ['fichiers', 'commentaires.commentateur', 'soumisPar', 'evaluateur', 'validateur', 'projet']
            );

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $tdrFinal
            ], $statusCode);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Sauvegarder les champs dynamiques basés sur le canevas TDR
     */
    private function saveDynamicFieldsFromCanevas(Tdr $tdrPrefaisabilite, array $champsData, $canevasTdr): void
    {
        // Récupérer tous les champs du canevas TDR
        $champsDefinitions = $canevasTdr->all_champs;

        // Indexer par attribut pour accès rapide
        $champsMap = $champsDefinitions->keyBy('attribut');

        $syncData = [];

        foreach ($champsData as $attribut => $valeur) {
            if (isset($champsMap[$attribut])) {
                $champ = $champsMap[$attribut];

                // Le cast JSON du modèle ChampProjet gère automatiquement tout type
                $syncData[$champ->id] = [
                    'valeur' => $valeur, // Peut être string, array, object, number, boolean, etc.
                    'commentaire' => null
                ];
            }
        }

        // Synchroniser tous les champs reçus
        if (!empty($syncData)) {
            $tdrPrefaisabilite->champs()->sync($syncData);
        }
    }

    /**
     * Gérer les documents/fichiers attachés au TDR
     */
    private function handleDocuments(Tdr $tdr, array $documentsData): void
    {
        foreach ($documentsData as $index => $file) {
            if ($file) {
                // Sauvegarder le document avec la même logique que le fichier TDR
                $this->sauvegarderAutreDocument($tdr, $file, [], $index + 1);
            }
        }
    }

    /**
     * Sauvegarder un autre document avec version (même logique que sauvegarderFichierTdr)
     */
    private function sauvegarderAutreDocument(Tdr $tdr, $fichier, array $data, int $ordre = 1): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();

        // Créer ou récupérer la structure de dossiers pour autres documents
        $dossierTdr = $this->getOrCreateTdrFolderStructure($tdr->projet_id, 'autres-documents');

        // Hasher l'identifiant BIP pour le stockage physique
        $hashedIdentifiantBip = hash('sha256', $tdr->projet->identifiant_bip);

        // Générer un nom de fichier unique avec timestamp
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $nomOriginal;

        // Créer le chemin basé sur la structure de dossiers en base de données (avec hash pour stockage)
        $cheminStockage = $dossierTdr ?
            $dossierTdr->full_path :
            'projets/' . $hashedIdentifiantBip . '/Evaluation ex-ante/Etude de préfaisabilité/Termes de référence/Autres documents';

        // Nettoyer le chemin pour le stockage physique (éliminer espaces et caractères spéciaux)
        $cheminStockagePhysique = strtolower(SlugHelper::generateFilePath($cheminStockage));

        // Créer le dossier s'il n'existe pas
        \Storage::disk('local')->makeDirectory($cheminStockagePhysique);

        $chemin = $fichier->storeAs($cheminStockagePhysique, $nomStockage, 'local');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => $data['description'] ?? 'Autre document pour TDR de préfaisabilité',
            'commentaire' => $data['commentaire'] ?? null,
            'metadata' => [
                'type_document' => 'autre-document-prefaisabilite',
                'tdr_id' => $tdr->id,
                'projet_id' => $tdr->projet_id,
                'ordre' => $ordre,
                'statut' => 'actif',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()->toISOString(),
                'uploaded_context' => 'tdr-prefaisabilite-autres-documents',
                'dossier_public' => $dossierTdr ? $dossierTdr->full_path : 'Projets/' . $tdr->projet->identifiant_bip . '/Evaluation ex-ante/Etude de préfaisabilité/Termes de référence/Autres documents'
            ],
            'fichier_attachable_id' => $tdr->id,
            'fichier_attachable_type' => \App\Models\Tdr::class,
            'categorie' => 'tdr-prefaisabilite',
            'dossier_id' => $dossierTdr?->id,
            'ordre' => $ordre,
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);
    }

    /**
     * Méthode de mise à jour simplifiée - utilise la logique de create
     */
    public function update($id, array $data): JsonResponse
    {
        try {
            // Récupérer la note conceptuelle pour obtenir le projetId
            $tdrPrefaisabilite = $this->repository->findOrFail($id);
            // Mettre à jour la note existante
            $tdrPrefaisabilite->update($data);

            $message = 'Tdr de préfaisabilité mise à jour avec succès.';
            $statusCode = 200;

            // Récupérer le canevas de rédaction de note conceptuelle
            $canevasNoteConceptuelle = $this->documentRepository->getModel()->where([
                'type' => 'formulaire'
            ])->whereHas('categorie', function ($query) {
                $query->where('slug', 'canevas-tdr-prefaisabilite');
            })->orderBy('created_at', 'desc')->first();

            if ($canevasNoteConceptuelle) {
                // Sauvegarder les champs dynamiques basés sur le canevas
                $this->saveDynamicFieldsFromCanevas($tdrPrefaisabilite, $data, $canevasNoteConceptuelle);
            }

            $tdrPrefaisabilite->note_conceptuelle = $tdrPrefaisabilite->champs->map(function ($champ) {
                return [
                    'id' => $champ->id,
                    'label' => $champ->label,
                    'attribut' => $champ->attribut,
                    'valeur' => $champ->pivot->valeur,
                    'commentaire' => $champ->pivot->commentaire,
                    'updated_at' => $champ->pivot->updated_at
                ];
            });

            $tdrPrefaisabilite->save();

            if ($tdrPrefaisabilite->projet->statut == StatutIdee::NOTE_CONCEPTUEL) {
                $tdrPrefaisabilite->projet->update([
                    'statut' => StatutIdee::VALIDATION_NOTE_AMELIORER,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::VALIDATION_NOTE_AMELIORER),
                    'type_projet' => TypesProjet::simple
                ]);
            }


            return (new $this->resourceClass($tdrPrefaisabilite))
                ->additional(['message' => $message])
                ->response()
                ->setStatusCode($statusCode);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails des TDRs de préfaisabilité soumis
     */
    public function getTdrDetails(int $projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer le TDR le plus récent pour ce projet
            $tdr = $this->tdrRepository->getModel()
                ->where('projet_id', $projetId)
                ->where('type', 'prefaisabilite')
                ->with(['soumisPar', 'redigerPar', 'fichiers.uploadedBy'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$tdr) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucun TDR de préfaisabilité trouvé pour ce projet.'
                ], 206);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    //'projet' => new ProjetsResource($projet->load('tdrPrefaisabilite')),
                    'tdr' => new TdrResource($tdr->load("projet", "historique_des_tdrs_prefaisabilite")),
                    //'fichiers' => $tdr->fichiers,
                    //'peut_apprecier' => $projet->statut->value === StatutIdee::TDR_PREFAISABILITE->value,
                    'statut_projet' => $projet->statut,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la récupération des détails du TDR. " . $e->getMessage(),
                'error' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }

    /**
     * Soumettre les TDRs de préfaisabilité (SFD-010)
     */
    public function soumettreTdrs($projetId, array $data): JsonResponse
    {
        try {

            if (!auth()->user()->hasPermissionTo('soumettre-un-tdr-de-prefaisabilite') && auth()->user()->type !== 'dpaf' && auth()->user()->profilable_type !== Dpaf::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            DB::beginTransaction();

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::TDR_PREFAISABILITE->value, StatutIdee::R_TDR_PREFAISABILITE->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de soumission des TDRs de préfaisabilité.'
                ], 422);
            }

            // Extraire les données spécifiques au payload
            $estSoumise = $data['est_soumise'] ?? true;

            // Déterminer le statut selon est_soumise
            $statut = $estSoumise ? 'soumis' : 'brouillon';

            // Extraire les données spécifiques au payload
            //$champsData = $data['champs'] ?? [];
            $documentsData = $data['autres_document'] ?? [];

            // Préparer les données du TDR
            $tdrData = [
                'projet_id' => $projetId,
                'type' => 'prefaisabilite',
                'statut' => $statut,
                'resume' => $data['resume_tdr_prefaisabilite'] ?? 'TDR de préfaisabilité',
                'termes_de_reference' => [],
                'date_soumission' => $estSoumise ? now() : null,
                'soumis_par_id' => auth()->id(),
                'rediger_par_id' => auth()->id(),
            ];

            // Chercher un TDR existant pour ce projet et type
            $tdrExistant = \App\Models\Tdr::where('projet_id', $projetId)
                ->where('type', 'prefaisabilite')
                ->orderBy("created_at", "desc")
                ->first();

            if ($tdrExistant && $tdrExistant->statut === 'soumis') {
                // Si un TDR soumis existe déjà, créer une nouvelle version avec parent_id
                $tdrData['parent_id'] = $tdrExistant->id;
                $tdr = \App\Models\Tdr::create($tdrData);
                $message = 'Nouvelle version du TDR de préfaisabilité créée avec succès.';
            } elseif ($tdrExistant && ($tdrExistant->statut === 'brouillon' ||  $tdrExistant->statut === 'retour_travail_supplementaire')) {
                // Si un TDR non soumis existe, le mettre à jour
                $tdr = $tdrExistant;
                $tdr->fill($tdrData);
                $tdr->save();
                $message = 'TDR de préfaisabilité mis à jour avec succès.';
            } else {
                // Créer un nouveau TDR (première version)
                $tdr = \App\Models\Tdr::create($tdrData);
                $message = 'TDR de préfaisabilité créé avec succès.';
            }

            if (isset($data["numero_dossier"])) {
                $tdr->update([
                    "numero_dossier" => $data["numero_dossier"]
                ]);
            }

            if (isset($data["numero_contrat"])) {
                $tdr->update([
                    "numero_contrat" => $data["numero_contrat"]
                ]);
            }

            /*
            // Récupérer le canevas de rédaction TDR préfaisabilité
            $canevasTdr = $this->documentRepository->getModel()->where([
                'type' => 'formulaire'
            ])->whereHas('categorie', function ($query) {
                $query->where('slug', 'canevas-redaction-tdr-prefaisabilite');
            })->orderBy('created_at', 'desc')->first();

            if ($canevasTdr) {
                // Sauvegarder les champs dynamiques basés sur le canevas
                $this->saveDynamicFieldsFromCanevas($tdr, $champsData, $canevasTdr);
            }
            */

            // Gérer les documents/fichiers
            if (!empty($documentsData)) {
                $this->handleDocuments($tdr, $documentsData);
            }

            // Traitement et sauvegarde du fichier TDR (legacy)
            $fichierTdr = null;
            if (isset($data['tdr'])) {
                $fichierTdr = $this->sauvegarderFichierTdr($tdr, $data['tdr'], $data);
            }

            $projet->resume_tdr_prefaisabilite = $data["resume_tdr_prefaisabilite"];

            // Cas spécifique : Resoumission d'un TDR retourné (R_TDR_PREFAISABILITE ou TDR_PREFAISABILITE)
            if ($estSoumise && in_array($projet->statut, [StatutIdee::R_TDR_PREFAISABILITE, StatutIdee::TDR_PREFAISABILITE])) {
                // Si le TDR a un parent, créer une nouvelle évaluation basée sur l'ancienne
                if ($tdr->parent_id) {
                    $ancienTdr = \App\Models\Tdr::find($tdr->parent_id);
                    if ($ancienTdr) {
                        $this->creerEvaluationPourTdrResoumis($tdr, $ancienTdr);
                    }
                }

                // Changer le statut du projet vers EVALUATION_TDR_PF
                $projet->update([
                    'statut' => StatutIdee::EVALUATION_TDR_PF,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::EVALUATION_TDR_PF),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::EVALUATION_TDR_PF)
                ]);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($projet, StatutIdee::EVALUATION_TDR_PF);
                $this->enregistrerDecision(
                    $projet,
                    "Resoumission du TDR de préfaisabilité après révision",
                    $data['resume_tdr_prefaisabilite'] ?? 'TDR révisé soumis pour réévaluation',
                    auth()->user()->personne->id
                );

                // Envoyer une notification
                $this->envoyerNotificationSoumission($projet, $fichierTdr);
            }

            // Changer le statut du projet seulement si est_soumise est true
            if ($estSoumise && !in_array($projet->statut, [StatutIdee::R_TDR_PREFAISABILITE, StatutIdee::TDR_PREFAISABILITE])) {
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
                    $data['resume_tdr_prefaisabilite'] ?? 'TDRs soumis pour évaluation',
                    auth()->user()->personne->id
                );

                // Envoyer une notification
                $this->envoyerNotificationSoumission($projet, $fichierTdr);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'tdr' => new TdrResource($tdr),
                    'fichier_id' => $fichierTdr ? $fichierTdr->id : null,
                    'projet_id' => $projet->id,
                    'ancien_statut' => in_array($projet->statut->value, [StatutIdee::TDR_PREFAISABILITE->value, StatutIdee::R_TDR_PREFAISABILITE->value]) ? $projet->statut->value : StatutIdee::TDR_PREFAISABILITE->value,
                    'nouveau_statut' => $estSoumise ? StatutIdee::EVALUATION_TDR_PF->value : $projet->statut->value,
                    'fichier_url' => $fichierTdr ? $fichierTdr->url : null,
                    'resume' => $data['resume'] ?? null,
                    'tdr_pre_faisabilite' => $data['tdr_pre_faisabilite'] ?? null,
                    'type_tdr' => $data['type_tdr'] ?? null,
                    'soumis_par' => auth()->id(),
                    'soumis_le' => $estSoumise ? now()->format('d/m/Y H:i:s') : null
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
    public function evaluerTdrs($projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!auth()->user()->hasPermissionTo('apprecier-un-tdr-de-prefaisabilite') && auth()->user()->type !== 'dgpd' && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n\'avez pas les droits pour effectuer cette évaluation.", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::EVALUATION_TDR_PF->value, StatutIdee::R_TDR_PREFAISABILITE->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape d\'évaluation des TDRs.'
                ], 422);
            }

            // Vérifier qu'il y a un TDR soumis
            $tdr = $this->tdrRepository->getModel()
                ->where('projet_id', $projetId)
                ->where('type', 'prefaisabilite')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$tdr) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun TDR de préfaisabilité trouvé pour ce projet.'
                ], 404);
            }

            // Vérifier que le TDR est soumis et peut être évalué
            if (!$tdr->peutEtreEvalue()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le TDR doit être soumis avant de pouvoir être évalué.'
                ], 422);
            }

            if ($data["evaluer"]) {
                // Enregistrer les appréciations pour chaque champ
                if (!isset($data['evaluations_champs'])) {
                    throw ValidationException::withMessages(["evaluations_champs" => "Veuillez apprecier le canevas "]);
                }

                /*if (!isset($data["numero_dossier"])) {
                    throw ValidationException::withMessages([
                        "numero_dossier" => "Le numéro du dossier est obligatoire pour l'évaluation."
                    ]);
                }

                if (!isset($data["numero_contrat"])) {
                    throw ValidationException::withMessages([
                        "numero_contrat" => "Le numéro du contrat est obligatoire pour l'évaluation."
                    ]);
                }*/

                if (!isset($data["accept_term"])) {
                    throw ValidationException::withMessages([
                        "accept_term" => "Vous devez accepter les termes pour poursuivre l'évaluation."
                    ]);
                }
            }

            if (isset($data["accept_term"])) {
                $tdr->update([
                    "accept_term" => $data["accept_term"]
                ]);
            }

            $tdr->save();

            $tdr->refresh();

            // Créer ou mettre à jour l'évaluation
            $evaluation = $this->creerEvaluationTdr($tdr, $data);

            // Calculer le résultat de l'évaluation selon les règles SFD-011
            $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, $data);

            // Préparer l'évaluation complète pour enregistrement
            $evaluationComplete = [
                'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationTdrPrefaisabilite()->all_champs)->map(function ($champ) use ($evaluation) {
                    $champEvalue = collect($evaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                    return [
                        'champ_id' => $champ['id'],
                        'label' => $champ['label'],
                        'attribut' => $champ['attribut'],
                        'ordre_affichage' => $champ['ordre_affichage'],
                        'type_champ' => $champ['type_champ'],
                        'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                        'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                        'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                    ];
                })->toArray(),

                'statistiques' => $resultatsEvaluation,
                'date_evaluation' => now(),
                'confirme_par' => $data["evaluer"] ? new UserResource(auth()->user()) : null
            ];

            // Mettre à jour l'évaluation avec les données complètes
            $evaluation->fill([
                'resultats_evaluation' => $resultatsEvaluation,
                'evaluation' => $evaluationComplete
            ]);

            $evaluation->save();

            if ($data["evaluer"]) {

                $tdr->statut = "en_evaluation";
                $tdr->save();

                // Mettre à jour l'évaluation avec les données complètes
                $evaluation->fill([
                    'valider_par' => $data["evaluer"] ? auth()->id() : null,
                    'valider_le' => $data["evaluer"] ? now() : null,
                ]);

                // Traiter la décision selon le résultat (changement automatique du statut)
                $nouveauStatut = $this->traiterDecisionEvaluationTdrAutomatique($projet, $resultatsEvaluation, $tdr, $evaluation);

                // Enregistrer le workflow et la décision
                $this->enregistrerWorkflow($projet, $nouveauStatut);
                $this->enregistrerDecision(
                    $projet,
                    "Évaluation des TDRs de préfaisabilité - " . ucfirst($resultatsEvaluation['resultat_global']),
                    $data['commentaire'] ?? $resultatsEvaluation['message_resultat'],
                    auth()->user()->personne->id
                );
            }

            DB::commit();

            if ($data["evaluer"]) {
                // Envoyer une notification
                $this->envoyerNotificationEvaluation($projet, $resultatsEvaluation);
            }

            return response()->json([
                'success' => true,
                'message' => $this->getMessageSuccesEvaluation($resultatsEvaluation['resultat_global']),
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'evaluation' => $evaluation,
                    'projet_id' => $projet->id,
                    'resultat_global' => $resultatsEvaluation['resultat_global'],
                    'evaluateur_id' => auth()->id(),
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
     * Récupérer les détails d'évaluation d'un TDR
     */
    public function getEvaluationTdr(int $projetId): JsonResponse
    {
        try {

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Récupérer le TDR soumis (pas en brouillon)
            $tdr = $this->tdrRepository->getModel()
                ->where('projet_id', $projetId)
                ->where('type', 'prefaisabilite')
                ->where('statut', '!=', 'brouillon')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$tdr) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Aucun TDR de préfaisabilité soumis trouvé pour ce projet.'
                ], 404);
            }

            // Récupérer l'évaluation en cours ou la dernière évaluation via le TDR
            $evaluation = $tdr->evaluations()
                ->where('type_evaluation', 'tdr-prefaisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => true,
                    'data' =>  [
                        'tdr' => new TdrResource($tdr->load(['fichiers', 'projet', "historique_des_evaluations_tdrs_prefaisabilite"])),
                    ],
                    'message' => 'Aucune évaluation trouvée pour cette tdr.'
                ], 206);
            }

            // Construire la grille d'évaluation avec les données existantes
            $grilleEvaluation = [];

            // Calculer le résultat de l'évaluation si elle existe et est terminée
            $resultatsEvaluation = null;
            $actionsSuivantes = null;
            $evaluationsChamps = [];

            if ($evaluation && $evaluation->statut == 1) {

                // Recalculer le résultat pour l'évaluation terminée
                $champs_evalues = is_string($evaluation->evaluation) ? json_decode($evaluation->evaluation)->champs_evalues : $evaluation->evaluation["champs_evalues"];

                foreach ($champs_evalues as $champ) {
                    $champ =  (array)$champ;
                    $grilleEvaluation[] = [
                        'champ_id' => isset($champ["champ_id"]) ? $champ["champ_id"] : null,
                        'label' => isset($champ["label"]) ? $champ["label"] : null,
                        'attribut' => isset($champ["attribut"]) ? $champ["attribut"] : null,
                        'type_champ' => isset($champ["type_champ"]) ? $champ["type_champ"] : "textearea",
                        'ordre_affichage' => isset($champ["ordre_affichage"]) ? $champ["ordre_affichage"] : 0,
                        'appreciation' =>  isset($champ["appreciation"]) ? $champ["appreciation"] : null,
                        'commentaire_evaluateur' =>  isset($champ["commentaire_evaluateur"]) ? $champ["commentaire_evaluateur"] : null,
                        'date_appreciation' =>  isset($champ["date_appreciation"]) ? $champ["date_appreciation"] : null,
                    ];
                }
                $resultatsEvaluation = $evaluation->resultats_evaluation;
            } else {

                // Récupérer le canevas d'appréciation des TDRs
                $canevasAppreciation = $this->documentRepository->getModel()
                    ->where('type', 'checklist')
                    ->where('slug', 'canevas-appreciation-tdrs-prefaisabilite')
                    ->with(['champs' => function ($query) {
                        $query->orderBy('ordre_affichage');
                    }])
                    ->first();

                if (!$canevasAppreciation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Canevas d\'appréciation des TDRs introuvable.'
                    ], 404);
                }

                foreach ($canevasAppreciation->all_champs as $champ) {
                    $evaluationExistante = null;
                    $data = [];
                    if ($evaluation) {

                        // On commence par vérifier si la structure "evaluation['champs_evalues']" existe
                        if (isset($evaluation->evaluation['champs_evalues'])) {
                            $evaluationExistante = collect($evaluation->evaluation['champs_evalues'])
                                ->firstWhere('champ_id', $champ->id);

                            if ($evaluationExistante) {
                                $data = [
                                    'appreciation' => $evaluationExistante["appreciation"] ?? null,
                                    'commentaire_evaluateur' => $evaluationExistante["commentaire_evaluateur"] ?? null,
                                    'date_evaluation' => $evaluationExistante["date_appreciation"] ?? null
                                ];
                            }
                        }
                        // Sinon, on vérifie la relation directe "champs_evalues"
                        elseif (isset($evaluation->champs_evalues)) {
                            $evaluationExistante = $evaluation->champs_evalues
                                ->firstWhere('id', $champ->id);

                            if ($evaluationExistante) {
                                $data = [
                                    'appreciation' => $evaluationExistante->pivot->note ?? null,
                                    'commentaire_evaluateur' => $evaluationExistante->pivot->commentaires ?? null,
                                    'date_evaluation' => $evaluationExistante->pivot->date_note ?? null
                                ];
                            }
                        }

                        if ($evaluationExistante) {
                            // Convertir en tableau si c'est un objet pour faciliter l'accès
                            $evalArray = is_array($evaluationExistante) ? $evaluationExistante : (array)$evaluationExistante;

                            // Vérifie et ajoute les champs "_passer" uniquement si les clés existent
                            if (isset($evalArray['appreciation_passer'])) {
                                $data['appreciation_passer'] = $evalArray['appreciation_passer'];
                            }

                            if (isset($evalArray['commentaire_passer_evaluateur'])) {
                                $data['commentaire_passer_evaluateur'] = $evalArray['commentaire_passer_evaluateur'];
                            }

                            if (isset($evalArray['date_appreciation_passer'])) {
                                $data['date_appreciation_passer'] = $evalArray['date_appreciation_passer'];
                            }
                        }
                    }

                    $grilleEvaluation[] = array_merge([
                        'champ_id' => $champ->id,
                        'label' => $champ->label,
                        'attribut' => $champ->attribut,
                        'type_champ' => $champ->type_champ,
                        'ordre_affichage' => $champ->ordre_affichage,
                        'appreciation' => $data['appreciation'] ?? null,
                        'commentaire_evaluateur' => $data['commentaire_evaluateur'] ?? null,
                        'date_evaluation' => $data['date_evaluation'] ?? null,
                    ], $data);
                }

                $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, ['evaluations_champs' => $grilleEvaluation]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Détails de l\'évaluation TDR récupérés avec succès.',
                'data' => [
                    'tdr' => new TdrResource($tdr->load(['fichiers', 'projet', "historique_des_evaluations_tdrs_prefaisabilite"])),
                    'evaluation_existante' => $evaluation ? [
                        'id' => $evaluation->id,
                        'statut' => $evaluation->statut, // 0=en cours, 1=terminée
                        'evaluateur' => new UserResource($evaluation->evaluateur),
                        'date_debut' => Carbon::parse($evaluation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                        'date_fin' => Carbon::parse($evaluation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                        'commentaire_global' => $evaluation->commentaire,
                        'grille_evaluation' => $grilleEvaluation,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $evaluation->resultats_evaluation,
                    ] : null,
                    'resultats_evaluation' => $resultatsEvaluation,
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function getEvaluation(int $projetId): JsonResponse
    {
        try {
            //$noteConceptuelle = $this->repository->findOrFail($noteConceptuelleId);
            $projet = $this->projetRepository->findOrFail($projetId);

            /* $evaluation = $this->evaluationRepository->getModel()
                ->where('projetable_type', NoteConceptuelle::class)
                ->where('projetable_id', $noteConceptuelle->id)
                ->where('type_evaluation', 'note_conceptuelle')
                ->with(['evaluateur', 'validator'])
                //->orderBy('created_at', 'desc')
                ->first(); */

            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $tdr = $projet->tdrPrefaisabilite->first();

            if (!$tdr) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Aucun TDR de préfaisabilité trouvé pour ce projet.'
                ], 404);
            }

            $evaluation = $tdr->evaluationPrefaisabiliteEnCours();

            if (!$evaluation) {
                $evaluation = $tdr->evaluationPrefaisabiliteTerminer();

                if (!$evaluation) {
                    return response()->json([
                        'success' => true,
                        'data' => null,
                        'message' => 'Aucune évaluation trouvée pour cette note conceptuelle.'
                    ], 206);
                }
            }

            if (!$evaluation) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucune évaluation trouvée pour cette note conceptuelle.'
                ], 206);
            }

            // Calculer les résultats d'examen
            $resultatsExamen = $evaluation->statut ? $evaluation->resultats_evaluation :  $this->calculerResultatEvaluationTdr($evaluation, ['evaluations_champs' => $evaluation->evaluation['champs_evalues']]);

            // Déterminer les actions suivantes selon le résultat
            $actionsSuivantes = $this->getActionsSuivantesSelonResultat($resultatsExamen['resultat_global']);

            return response()->json([
                'success' => true,
                'data' => [
                    'tdr' => new TdrResource($tdr->load(['fichiers', 'projet'])),
                    'evaluation' => [
                        'id' => $evaluation->id,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'date_debut_evaluation' => $evaluation->date_debut_evaluation ? Carbon::parse($evaluation->date_debut_evaluation)->format("d/m/Y H:m:i") : null,
                        'date_fin_evaluation' => $evaluation->date_fin_evaluation ? Carbon::parse($evaluation->date_fin_evaluation)->format("d/m/Y H:m:i") : null,
                        'valider_le' => $evaluation->valider_le ? Carbon::parse($evaluation->valider_le)->format("d/m/Y H:m:i") : null,
                        'valider_par' => $evaluation->valider_par,
                        'commentaire' => $evaluation->commentaire,
                        'evaluation' => $evaluation->evaluation,
                        'resultats_evaluation' => $resultatsExamen, //($evaluation->statut && $noteConceptuelle->projet->statut != StatutIdee::EVALUATION_NOTE) ? $evaluation->resultats_evaluation : $resultatsExamen,
                        'statut' => $evaluation->statut,
                        //'champs' => collect($noteConceptuelle->note_conceptuelle)->map(function ($champ) use ($evaluation) {
                        'champs' => collect($this->documentRepository->getCanevasAppreciationNoteConceptuelle()->all_champs)->map(function ($champ) use ($evaluation) {
                            $champ_evalue = collect($evaluation->champs_evalue)
                                ->firstWhere('attribut', $champ["attribut"]);

                            // Récupérer aussi les anciennes valeurs depuis evaluation JSON
                            $champsEvaluesJSON = collect($evaluation->evaluation['champs_evalues'] ?? []);
                            $champEvalueJSON = $champsEvaluesJSON->firstWhere('attribut', $champ["attribut"]);

                            $result = [
                                'id' => $champ["id"],
                                'label' => $champ["label"],
                                'attribut' => $champ["attribut"],
                                'valeur' => $champ["valeur"],
                                'appreciation' => $champ_evalue ? $champ_evalue["pivot"]["note"] : null,
                                'commentaire' => $champ_evalue ? $champ_evalue["pivot"]["commentaires"] : null,
                                'date_note' => $champ_evalue ? $champ_evalue["pivot"]["date_note"] : null,
                                'updated_at' => $champ_evalue ? $champ_evalue["pivot"]["updated_at"] : null,
                            ];

                            // Ajouter les anciennes valeurs "_passer" si elles existent
                            if ($champEvalueJSON && isset($champEvalueJSON['appreciation_passer'])) {
                                $result['appreciation_passer'] = $champEvalueJSON['appreciation_passer'];
                                $result['commentaire_passer_evaluateur'] = $champEvalueJSON['commentaire_passer_evaluateur'] ?? null;
                                $result['date_appreciation_passer'] = $champEvalueJSON['date_appreciation_passer'] ?? null;
                            }

                            return $result;
                        }),
                    ],
                    'actions_suivantes' => $actionsSuivantes,
                    'resultats_examen' =>  $resultatsExamen, //($evaluation->statut && $noteConceptuelle->projet->statut != StatutIdee::EVALUATION_NOTE) ? $evaluation->resultats_evaluation : $resultatsExamen
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Valider les TDRs de préfaisabilité (décision finale pour cas "non accepté" uniquement)
     */
    public function validerTdrs($projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!auth()->user()->hasPermissionTo('apprecier-un-tdr-de-prefaisabilite') && auth()->user()->type !== 'dgpd' && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n\'avez pas les droits pour effectuer cette évaluation.", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if (!in_array($projet->statut->value, [StatutIdee::EVALUATION_TDR_PF->value, StatutIdee::R_TDR_PREFAISABILITE->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape d\'évaluation des TDRs.'
                ], 422);
            }

            $tdr = $projet->tdrPrefaisabilite->first();

            // Vérifier que le TDR est soumis et peut être évalué
            if (!$tdr?->peutEtreValide()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le TDR doit être évalué avant de pouvoir être validé.'
                ], 422);
            }

            // Vérifier qu'il y a une évaluation terminée avec résultat "non accepté"
            $evaluation = $tdr?->evaluations()
                ->where('type_evaluation', 'tdr-prefaisabilite')
                ->where('statut', 1) // Évaluation terminée
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation terminée trouvée pour ce projet.'
                ], 422);
            }

            // Recalculer le résultat pour s'assurer qu'il est "non accepté"
            $evaluationsChamps = [];
            foreach ($evaluation->champs_evalue as $champ) {
                $evaluationsChamps[] = [
                    'champ_id' => $champ->id,
                    'appreciation' => $champ->pivot->note,
                    'commentaire' => $champ->pivot->commentaires
                ];
            }

            $resultatsEvaluation = $this->calculerResultatEvaluationTdr($evaluation, ['evaluations_champs' => $evaluationsChamps]);

            if ($resultatsEvaluation['resultat_global'] !== 'non_accepte') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette méthode n\'est utilisable que pour les cas "non accepté". Le résultat actuel est: ' . $resultatsEvaluation['resultat_global']
                ], 422);
            }

            // Valider l'action demandée pour les cas "non accepté"
            if (!isset($data['action']) || !in_array($data['action'], ['reviser', 'abandonner'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action invalide pour cas "non accepté". Actions possibles: reviser, abandonner.'
                ], 422);
            }

            $nouveauStatut = null;
            $messageAction = '';

            switch ($data['action']) {
                case 'reviser':
                    // Reviser malgré l'évaluation négative → retour au statut TDR_PREFAISABILITE

                    $tdr = $projet->tdrPrefaisabilite->first();

                    if (!$tdr) {
                        return response()->json([
                            'success' => false,
                            'data' => null,
                            'message' => 'Aucun TDR de préfaisabilité trouvé pour ce projet.'
                        ], 404);
                    }

                    $tdr->update([
                        'decision_validation' => 'reviser',
                        'commentaire_validation' => $resultatsEvaluation["message_resultat"],
                    ]);

                    $tdrData = ([
                        'projet_id' => $tdr->projet->id,
                        'parent_id' => $tdr->id,
                        'type' => $tdr->type,
                        'statut' => 'brouillon',
                        'resume' => null,
                        'date_soumission' => null,
                        'soumis_par_id' => null,
                        'rediger_par_id' => $tdr->rediger_par_id,
                        'date_evaluation' => null,
                        'date_validation' => null,
                        'evaluateur_id' => null,
                        'validateur_id' => null,
                        'evaluations_detaillees' => [],
                        'termes_de_reference' => null,
                        'commentaire_evaluation' => null,
                        'commentaire_validation' => null,
                        'decision_validation' => null,
                        'resultats_evaluation' => null,
                        'numero_contrat' => null,
                        'numero_dossier' => null,
                        'accept_term' => false,
                        'canevas_appreciation_tdr' => $tdr->canevas_appreciation_tdr,
                    ]);

                    $newTdr = $tdr->projet->tdrs()->create($tdrData);

                    // Récupérer l'évaluation terminée du TDR
                    $evaluationTerminee = $evaluation;

                    if ($evaluationTerminee) {
                        // Créer une nouvelle évaluation liée au nouveau TDR
                        $newEvaluation = $evaluationTerminee->replicate();
                        $newEvaluation->projetable_id = $newTdr->id;
                        $newEvaluation->projetable_type = get_class($newTdr);
                        $newEvaluation->id_evaluation = $evaluationTerminee->id; // Lien vers l'évaluation parent
                        $newEvaluation->canevas = $evaluationTerminee->canevas; // Copier le canevas
                        $newEvaluation->statut = 0; // En cours
                        $newEvaluation->date_debut_evaluation = now();
                        $newEvaluation->date_fin_evaluation = null;
                        $newEvaluation->valider_le = null;
                        $newEvaluation->valider_par = null;
                        $newEvaluation->resultats_evaluation = [];

                        // Sauvegarder d'abord la nouvelle évaluation avec des valeurs temporaires
                        $newEvaluation->evaluation = [];
                        $newEvaluation->resultats_evaluation = [];
                        $newEvaluation->created_at = now();
                        $newEvaluation->updated_at = null;
                        $newEvaluation->save();

                        // Copier les relations champs_evalue de l'ancienne évaluation
                        // Pour les champs "passé" : copier tel quel
                        // Pour les autres (retour/non_accepte) : mettre null pour forcer la réévaluation
                        $champsEvalues = $evaluationTerminee->champs_evalue;
                        foreach ($champsEvalues as $champ) {
                            $note = $champ->pivot->note;

                            if ($note === 'passe') {
                                // Si passé, copier tel quel
                                $newEvaluation->champs_evalue()->attach($champ->id, [
                                    'note' => $note,
                                    'date_note' => $champ->pivot->date_note,
                                    'commentaires' => $champ->pivot->commentaires,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            } else {
                                // Si retour ou non_accepte, mettre null (pas de copie dans pivot)
                                // Les anciennes valeurs seront dans le JSON evaluation avec le suffixe "_passer"
                            }
                        }

                        // Recharger pour avoir accès aux relations
                        $newEvaluation->refresh();

                        // Construire le JSON evaluation basé sur les champs copiés
                        $resultatsExamen = $this->calculerResultatEvaluationTdr($newEvaluation, []);

                        // Récupérer l'ancienne évaluation pour référence
                        $ancienneEvaluation = $evaluationTerminee->evaluation ?? [];
                        $anciensChampsEvalues = collect($ancienneEvaluation['champs_evalues'] ?? []);

                        $evaluationComplete = [
                            'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationTdrPrefaisabilite()->all_champs)->map(function ($champ) use ($newEvaluation, $anciensChampsEvalues) {
                                $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                                $ancienChampEvalue = $anciensChampsEvalues->firstWhere('attribut', $champ['attribut']);

                                $result = [
                                    'champ_id' => $champ['id'],
                                    'label' => $champ['label'],
                                    'attribut' => $champ['attribut'],
                                    'ordre_affichage' => $champ['ordre_affichage'],
                                    'type_champ' => $champ['type_champ'],
                                    'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                                    'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                                    'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                                ];

                                // Si le champ n'est pas dans la nouvelle évaluation mais existe dans l'ancienne
                                // C'est un champ qui n'était pas "passé", on ajoute les anciennes valeurs avec "_passer"
                                if (!$champEvalue && $ancienChampEvalue) {
                                    $result['appreciation_passer'] = $ancienChampEvalue['appreciation'] ?? null;
                                    $result['commentaire_passer_evaluateur'] = $ancienChampEvalue['commentaire_evaluateur'] ?? null;
                                    $result['date_appreciation_passer'] = $ancienChampEvalue['date_appreciation'] ?? null;
                                }

                                return $result;
                            })->toArray(),
                            'statistiques' => $resultatsExamen
                        ];

                        // Mettre à jour avec les données complètes
                        $newEvaluation->evaluation = $evaluationComplete;
                        $newEvaluation->resultats_evaluation = $resultatsExamen;
                        $newEvaluation->save();
                    }

                    $nouveauStatut = StatutIdee::TDR_PREFAISABILITE;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut)
                    ]);

                    $messageAction = 'Projet continue malgré l\'évaluation négative. Retour à la soumission des TDRs.';
                    break;

                case 'abandonner':

                    $tdr->update([
                        'decision_validation' => 'abandonner',
                        'commentaire_validation' => $resultatsEvaluation["message_resultat"],
                    ]);

                    // Abandonner le projet suite à l'évaluation négative
                    $nouveauStatut = StatutIdee::ABANDON;
                    $projet->update([
                        'date_fin_etude' => now(),
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut)
                    ]);
                    $messageAction = 'Projet abandonné suite à l\'évaluation négative des TDRs.';
                    break;
            }

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, $nouveauStatut);
            $this->enregistrerDecision(
                $projet,
                "Décision finale TDRs préfaisabilité - " . ucfirst($data['action']),
                $data['commentaire'] ?? $messageAction,
                auth()->user()->personne->id
            );

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationValidation($projet, $data['action'], $data);

            return response()->json([
                'success' => true,
                'message' => $messageAction,
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'projet_id' => $projet->id,
                    'action' => $data['action'],
                    'ancien_statut' => StatutIdee::EVALUATION_TDR_PF->value,
                    'nouveau_statut' => $nouveauStatut->value,
                    'commentaire' => $data['commentaire'] ?? null,
                    'decision_par' => auth()->id(),
                    'decision_le' => now()->format('d/m/Y H:i:s'),
                    'resultats_evaluation' => $resultatsEvaluation
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Valider l'étude de préfaisabilité (SFD-013)
     */
    public function validerEtudePrefaisabilite($projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!auth()->user()->hasPermissionTo('valider-une-etude-de-prefaisabilite') && auth()->user()->type !== 'dgpd' && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n\'avez pas les droits pour effectuer cette évaluation.", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::VALIDATION_PF->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de validation de préfaisabilité.'
                ], 422);
            }

            if ($data['action'] != 'sauvegarder') {

                if (empty(data_get($data, 'checklist_suivi_validation'))) {
                    throw ValidationException::withMessages([
                        "checklist_suivi_validation" => "Veuillez faire le suivi du rapport de préfaisabilité pour la validation !"
                    ]);
                }

                // Valider les informations de financement si le projet est marqué comme financé
                if (
                    isset($projet->info_etude_prefaisabilite['est_finance']) &&
                    !empty($projet->info_etude_prefaisabilite['est_finance'])
                ) {
                    $est_finance = $projet->info_etude_prefaisabilite['est_finance'];
                    // Convertir en booléen si nécessaire
                    if (is_string($est_finance)) {
                        $est_finance = strtolower($est_finance) === 'true' || $est_finance === '1';
                    } else {
                        $est_finance = filter_var($est_finance, FILTER_VALIDATE_BOOLEAN);
                    }

                    if ($est_finance) {

                        if (isset($data['etude_prefaisabilite'])) {
                            // si c'est une string JSON → on la décode
                            if (is_string($data['etude_prefaisabilite'])) {
                                throw new Exception("Error Processing Request", 1);

                                $decoded = json_decode($data['etude_prefaisabilite'], true);

                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $data['etude_prefaisabilite'] = $decoded;
                                } else {
                                    throw ValidationException::withMessages([
                                        "etude_prefaisabilite" => "Format JSON invalide pour les informations de financement."
                                    ]);
                                }
                            }
                            // si c'est déjà un tableau → on ne fait rien
                            elseif (!is_array($data['etude_prefaisabilite'])) {
                                throw ValidationException::withMessages([
                                    "etude_prefaisabilite" => "Les informations de financement doivent être un tableau valide."
                                ]);
                            }
                        } else {
                            throw ValidationException::withMessages([
                                "etude_prefaisabilite" => "Les informations de financement sont requises lorsque le projet est financé."
                            ]);
                        }

                        /* if (!isset($data['etude_prefaisabilite']) || empty($data['etude_prefaisabilite'])) {
                            throw ValidationException::withMessages([
                                "etude_prefaisabilite" => "Les informations de financement sont obligatoires lorsque le projet est financé."
                            ]);
                        }elseif(!is_string($data['etude_prefaisabilite']) || !is_array(json_decode($data['etude_prefaisabilite'], true))){
                            throw ValidationException::withMessages([
                                "etude_prefaisabilite" => "Les informations de financement sont invalides."
                            ]);
                        }


                        // Convertir la chaîne JSON en tableau associatif
                        $data['etude_prefaisabilite'] = (array) json_decode($data['etude_prefaisabilite'], true); */

                        $requiredFields = ['date_demande', 'date_obtention', 'montant', 'reference'];

                        foreach ($requiredFields as $field) {
                            // validation de présence de $data['etude_prefaisabilite'][$field]
                            //throw new Exception("Est_finance : $field " . "Form data" . json_encode($data["etude_prefaisabilite"]) . (!isset($data['etude_prefaisabilite'][$field]) && !empty($data['etude_prefaisabilite'][$field])));
                            if (!isset($data['etude_prefaisabilite'][$field]) && !empty($data['etude_prefaisabilite'][$field])) {
                                throw ValidationException::withMessages([
                                    "etude_prefaisabilite.$field" => "Le champ $field est obligatoire lorsque le projet est financé. " . $data['etude_prefaisabilite'][$field]
                                ]);
                            }
                            // validations supplémentaires pour les champs spécifiques
                            // Il faut savoir que les donnees sont soumis dans un formdata donc tout est string

                            if ($field === 'montant' && (!is_numeric($data['etude_prefaisabilite'][$field]) || $data['etude_prefaisabilite'][$field] <= 0)) {
                                throw ValidationException::withMessages([
                                    "etude_prefaisabilite.$field" => "Le montant doit être un nombre positif."
                                ]);
                            }

                            // Ajouter d'autres validations spécifiques si nécessaire
                            if (in_array($field, ['date_demande', 'date_obtention'])) {
                                $date = \DateTime::createFromFormat('Y-m-d', $data['etude_prefaisabilite'][$field]);
                                if (!$date || $date->format('Y-m-d') !== $data['etude_prefaisabilite'][$field]) {
                                    throw ValidationException::withMessages([
                                        "etude_prefaisabilite.$field" => "Le champ $field doit être une date valide au format AAAA-MM-JJ."
                                    ]);
                                }
                            }

                            if ($field === 'reference' && strlen($data['etude_prefaisabilite'][$field]) > 100) {
                                throw ValidationException::withMessages([
                                    "etude_prefaisabilite.$field" => "La référence ne doit pas dépasser 100 caractères."
                                ]);
                            }
                        }

                        // Toutes les validations sont passées, on peut enregistrer les informations
                        // enregistrer les informations de financement dans le projet info etude de préfaisabilité
                        // merge avec les données existantes pour ne pas écraser d'autres infos
                        $projet->info_etude_prefaisabilite = array_merge($projet->info_etude_prefaisabilite ?? [], [
                            'est_finance' => $est_finance, // pourquoi cette ligne ?
                            // recuperer les autres champs depuis $data

                            'date_demande' => $data['etude_prefaisabilite']['date_demande'],
                            'date_obtention' => $data['etude_prefaisabilite']['date_obtention'],
                            'montant' => $data['etude_prefaisabilite']['montant'],
                            'reference' => $data['etude_prefaisabilite']['reference'],
                        ]);


                        $projet->save();
                    }
                }
            }


            // Valider les informations de financement si le projet est marqué comme financé
            if (
                isset($projet->info_etude_prefaisabilite['est_finance']) &&
                !empty($projet->info_etude_prefaisabilite['est_finance'])
            ) {
                $est_finance = $projet->info_etude_prefaisabilite['est_finance'];
                // Convertir en booléen si nécessaire
                if (is_string($est_finance)) {
                    $est_finance = strtolower($est_finance) === 'true' || $est_finance === '1';
                } else {
                    $est_finance = filter_var($est_finance, FILTER_VALIDATE_BOOLEAN);
                }
                if ($est_finance) {

                    if (isset($data['etude_prefaisabilite'])) {
                        // si c'est une string JSON → on la décode
                        if (is_string($data['etude_prefaisabilite'])) {

                            $decoded = json_decode($data['etude_prefaisabilite'], true);

                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $data['etude_prefaisabilite'] = $decoded;
                            } else {
                                throw ValidationException::withMessages([
                                    "etude_prefaisabilite" => "Format JSON invalide pour les informations de financement."
                                ]);
                            }
                        }
                        // si c'est déjà un tableau → on ne fait rien
                        elseif (!is_array($data['etude_prefaisabilite'])) {
                            throw ValidationException::withMessages([
                                "etude_prefaisabilite" => "Les informations de financement doivent être un tableau valide."
                            ]);
                        }

                        $requiredFields = ['date_demande', 'date_obtention', 'montant', 'reference'];
                        $etude = [
                            'est_finance' => $est_finance
                        ];

                        foreach ($requiredFields as $field) {

                            if (isset($data['etude_prefaisabilite'][$field]) && !empty($data['etude_prefaisabilite'][$field])) {

                                if ($field === 'montant' && (!is_numeric($data['etude_prefaisabilite'][$field]) || $data['etude_prefaisabilite'][$field] <= 0)) {
                                    throw ValidationException::withMessages([
                                        "etude_prefaisabilite.$field" => "Le montant doit être un nombre positif."
                                    ]);
                                } elseif ($field === 'montant' && (is_numeric($data['etude_prefaisabilite'][$field]))) {
                                    $etude["montant"] = $data['etude_prefaisabilite']['montant'];
                                }

                                // Ajouter d'autres validations spécifiques si nécessaire
                                if (in_array($field, ['date_demande', 'date_obtention'])) {
                                    $date = \DateTime::createFromFormat('Y-m-d', $data['etude_prefaisabilite'][$field]);
                                    if (!$date || $date->format('Y-m-d') !== $data['etude_prefaisabilite'][$field]) {
                                        throw ValidationException::withMessages([
                                            "etude_prefaisabilite.$field" => "Le champ $field doit être une date valide au format AAAA-MM-JJ."
                                        ]);
                                    } else {
                                        $etude["$field"] = $data['etude_prefaisabilite'][$field];
                                    }
                                }

                                if ($field === 'reference' && strlen($data['etude_prefaisabilite'][$field]) > 100) {
                                    throw ValidationException::withMessages([
                                        "etude_prefaisabilite.$field" => "La référence ne doit pas dépasser 100 caractères."
                                    ]);
                                } else {
                                    $etude["reference"] = $data['etude_prefaisabilite']['reference'];
                                }
                            }
                        }

                        // Toutes les validations sont passées, on peut enregistrer les informations
                        // enregistrer les informations de financement dans le projet info etude de préfaisabilité
                        // merge avec les données existantes pour ne pas écraser d'autres infos
                        $projet->info_etude_prefaisabilite = array_merge($projet->info_etude_prefaisabilite ?? [], $etude);
                        $projet->save();
                    }
                }
            }

            /**
             * Valider l'étude de préfaisabilité selon l'action demandée
             * Actions possibles:
             * - maturite : Projet à maturité → passe au statut MATURITE
             * - faisabilite : Faire une étude de faisabilité → passe au statut TDR_FAISABILITE
             * - reprendre : Reprendre l'étude de préfaisabilité → retourne au statut ETUDE_PREFAISABILITE
             * - abandonner : Abandonner le projet → passe au statut ABANDON
             * - sauvegarder : Sauvegarder les données sans changer le statut
             *
             * Chaque action doit être justifiée par un commentaire.
             * Si l'action est "maturite" ou "faisabilite", le type de projet doit être mis à jour en conséquence.
             * Si l'action est "reprendre" ou "abandonner", le type de projet reste inchangé.
             * Si l'action est "sauvegarder", aucune modification de statut ou type de projet n'est effectuée.
             *
             */

            // Si le projet est "mou" (est_dur == false), exclure la possibilité de passer à l'étape de faisabilité
            if (
                isset($projet->est_dur) &&
                $projet->est_dur == false &&
                isset($data['action']) &&
                $data['action'] === 'faisabilite'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de passer à l\'étape de faisabilité pour un projet mou.'
                ], 422);
            }

            // Valider l'action demandée
            // Définir les actions permises selon la nature du projet (est_dur)
            if (isset($projet->est_dur) && $projet->est_dur === false) {
                // Si le projet est "mou", on exclut "faisabilite"
                $actionsPermises = ['maturite', 'reprendre', 'abandonner', 'sauvegarder'];
            } else {
                // Projet "dur" ou non défini, toutes les actions sont permises
                $actionsPermises = ['maturite', 'faisabilite', 'reprendre', 'abandonner', 'sauvegarder'];
            }

            if (!isset($data['action']) || !in_array($data['action'], $actionsPermises)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action invalide. Actions possibles: ' . implode(', ', $actionsPermises)
                ], 422);
            }

            $nouveauStatut = null;
            $messageAction = '';
            $typeProjet = null;

            // Créer une évaluation pour tracer la validation
            $evaluationValidation = $projet->evaluations()->updateOrCreate([
                'type_evaluation' => 'validation-etude-prefaisabilite',
                'projetable_type' => get_class($projet),
                'projetable_id' => $projet->id
            ], [
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => $data['action'] != 'sauvegarder' ? now() : null,
                'valider_le' => $data['action'] != 'sauvegarder' ? now() : null,
                'evaluateur_id' => auth()->id(),
                'valider_par' => auth()->id(),
                'commentaire' => $data['commentaire'] ?? $messageAction,
                'evaluation' => $data,
                'resultats_evaluation' => $data['action'],
                'statut' => $data['action'] != 'sauvegarder' ? 1 : 0
            ]);

            if ($evaluationValidation->statut) {

                /**
                 *
                 * enregistrer "synthèse et recommandations"* en tant que commentaire de l'etude de préfaisabilité
                 * valider la presence de la cle "synthese_recommandations" dans $data
                 * si elle est absente, lancer une exception
                 * si elle est presente, l'enregistrer dans le projet em tant que commentaire de l'etude de préfaisabilité en utilisant la relation polymorphiqye commentaires dans le projet
                 */
                if (!isset($data['synthese_recommandations']) || empty(trim($data['synthese_recommandations']))) {
                    throw ValidationException::withMessages([
                        "synthese_recommandations" => "La synthèse et recommandations est obligatoire pour la validation de l'étude de préfaisabilité."
                    ]);
                } else {
                    // Enregistrer la synthèse et recommandations en tant que commentaire
                    $projet->commentaires()->create([
                        'commentaire' => $data['synthese_recommandations'],
                        //'type_commentaire' => 'synthese_recommandations_prefaisabilite',
                        //'auteur_id' => auth()->id(),
                    ]);
                }
            }

            // Vérifier la cohérence du suivi rapport si des données de validation sont fournies
            if (isset($data['checklist_suivi_validation'])) {

                // Enregistrer les appréciations pour chaque champ

                $syncData = [];

                foreach ($data['checklist_suivi_validation'] as $evaluationChamp) {
                    $syncData[$evaluationChamp['checkpoint_id']] = [
                        'note' => $evaluationChamp['remarque'],
                        'date_note' => now(),
                        'commentaires' => $evaluationChamp['explication'] ?? null,
                    ];
                }

                $evaluationValidation->champs_evalue()->syncWithoutDetaching($syncData);

                // Préparer l'évaluation complète pour enregistrement
                $evaluationComplete = [
                    'champs_evalues' => collect($this->documentRepository->getCanevasChecklistSuiviRapportPrefaisabilite()->all_champs)->map(function ($champ) use ($evaluationValidation) {
                        $champEvalue = collect($evaluationValidation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                        return [
                            'champ_id' => $champ['id'],
                            'label' => $champ['label'],
                            'attribut' => $champ['attribut'],
                            'ordre_affichage' => $champ['ordre_affichage'],
                            'type_champ' => $champ['type_champ'],
                            'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                            'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                            'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                        ];
                    })->toArray(),
                    'decision' => ['decision' => $data['action'], 'commentaire' => $data['commentaire']],
                ];

                // Mettre à jour l'évaluation avec les données complètes
                $evaluationValidation->fill([
                    'evaluation' => $evaluationComplete,
                ]);

                $evaluationValidation->save();

                if (in_array($data['action'], ['maturite', 'faisabilite'])) {

                    /* $resultVerificationCoherence = $this->verifierCoherenceSuiviRapport($projet, $data['checklist_suivi_validation']);
                    if (!$resultVerificationCoherence['success']) {
                        return response()->json([
                            'success' => false,
                            'message' => $resultVerificationCoherence['message'],
                            'incoherences' => $resultVerificationCoherence['incoherences'] ?? []
                        ], 422);
                    } */

                    // Vérifier que tous les checkpoints obligatoires sont présents et complétés
                    /* $resultVerificationCompletude = $this->verifierCompletude($data['checklist_suivi_validation']);
                    if (!$resultVerificationCompletude['success']) {
                        return response()->json([
                            'success' => false,
                            'message' => $resultVerificationCompletude['message'],
                            'checkpoints_incomplets' => $resultVerificationCompletude['checkpoints_incomplets'] ?? []
                        ], 422);
                    } */
                }
            }

            switch ($data['action']) {
                case 'maturite':
                    // Projet à maturité
                    $nouveauStatut = StatutIdee::MATURITE;
                    $typeProjet = TypesProjet::complexe1;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
                        'type_projet' => $typeProjet,
                        'date_fin_etude' => now()
                    ]);
                    $messageAction = 'Projet validé comme étant à maturité.';
                    break;

                case 'faisabilite':
                    // Faire une étude de faisabilité
                    $nouveauStatut = StatutIdee::TDR_FAISABILITE;
                    $typeProjet = TypesProjet::complex2;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
                        'type_projet' => $typeProjet
                    ]);
                    $messageAction = 'Projet orienté vers une étude de faisabilité.';
                    break;

                case 'reprendre':
                    // Reprendre l'étude de préfaisabilité
                    $nouveauStatut = StatutIdee::SOUMISSION_RAPPORT_PF;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut)
                    ]);
                    $messageAction = 'Projet renvoyé pour reprendre l\'étude de préfaisabilité.';
                    break;

                case 'abandonner':
                    // Abandonner le projet
                    $nouveauStatut = StatutIdee::ABANDON;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
                        'date_fin_etude' => now()
                    ]);
                    $messageAction = 'Projet abandonné lors de la validation.';
                    break;

                case 'sauvegarder':
                    // Sauvegarder sans changer le statut
                    $this->sauvegarderDonneesValidation($projet, $data);
                    $messageAction = 'Données de validation sauvegardées sans changement de statut.';
                    // Pas de changement de statut
                    break;
                default:
                    // Récupérer l'ancien contenu JSON ou un tableau vide
                    $info = $projet->info_etude_prefaisabilite ?? [];

                    // Fusionner avec les nouvelles valeurs provenant de $data
                    $info = array_merge($info, [
                        'date_demande'   => $data['etude_prefaisabilite']['date_demande'] ?? null,
                        'date_obtention' => $data['etude_prefaisabilite']['date_obtention'] ?? null,
                        'montant'        => $data['etude_prefaisabilite']['montant'] ?? null,
                        'reference'      => $data['etude_prefaisabilite']['reference'] ?? null,
                    ]);

                    // Mettre à jour le modèle
                    $projet->update([
                        'info_etude_prefaisabilite' => $info,
                    ]);
            }

            // Attacher le fichier rapport de validation si fourni
            if (isset($data['rapport_validation_etude']) && $data['action'] !== 'sauvegarder') {
                $this->attacherFichierRapportValidation($projet, $data['rapport_validation_etude'], $evaluationValidation);

                // Enregistrer le workflow et la décision si le statut a changé
                if ($nouveauStatut) {
                    $this->enregistrerWorkflow($projet, $nouveauStatut);
                }

                $this->enregistrerDecision(
                    $projet,
                    "Validation préfaisabilité - " . ucfirst($data['action']),
                    $data['commentaire'] ?? $messageAction,
                    auth()->user()->personne->id
                );
            }


            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationValidationPrefaisabilite($projet, $data['action'], $data);

            return response()->json([
                'success' => true,
                'message' => $messageAction,
                'data' => [
                    'projet_id' => $projet->id,
                    'action' => $data['action'],
                    'ancien_statut' => StatutIdee::VALIDATION_PF->value,
                    'nouveau_statut' => $nouveauStatut ? $nouveauStatut->value : StatutIdee::VALIDATION_PF->value,
                    'type_projet' => $typeProjet ? $typeProjet->value : null,
                    'est_a_haut_risque' => $data['est_a_haut_risque'] ?? false,
                    'commentaire' => $data['commentaire'] ?? null,
                    'valide_par' => auth()->id(),
                    'valide_le' => now()->format('d/m/Y H:i:s'),
                    'date_fin_etude' => in_array($data['action'], ['maturite', 'abandonner']) ? now()->format('d/m/Y H:i:s') : null
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidationEtude(int $projetId): JsonResponse
    {
        try {

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que le projet est à l'étape d'évaluation ou post-évaluation
            if (!in_array($projet->statut->value, [
                StatutIdee::EVALUATION_TDR_PF->value,
                StatutIdee::SOUMISSION_RAPPORT_PF->value,
                StatutIdee::VALIDATION_PF->value,
                StatutIdee::R_TDR_PREFAISABILITE->value,
                StatutIdee::TDR_PREFAISABILITE->value,

                StatutIdee::EVALUATION_TDR_F->value,
                StatutIdee::SOUMISSION_RAPPORT_F->value,
                StatutIdee::VALIDATION_F->value,
                StatutIdee::R_TDR_FAISABILITE->value,
                StatutIdee::TDR_FAISABILITE->value,
                StatutIdee::PRET->value,
                StatutIdee::MATURITE->value,
                StatutIdee::RAPPORT->value,
                StatutIdee::ABANDON->value
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à une étape permettant la consultation des détails de validation.'
                ], 422);
            }

            // Récupérer l'évaluation en cours ou la dernière évaluation selon le statut
            $evaluationValidation = null;

            // Pour le statut VALIDATION_PF, récupérer l'évaluation de validation
            $evaluationValidation = $projet->evaluations()
                ->where('type_evaluation', 'validation-etude-prefaisabilite')
                ->with(['champs_evalue' => function ($query) {
                    $query->orderBy('ordre_affichage');
                }])
                ->orderBy('created_at', 'desc')
                ->first();

            // Récupérer les fichiers de validation attachés au projet
            $fichiersValidation = $projet->fichiers()
                ->where('categorie', 'rapport-validation-prefaisabilite')
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Détails de validation récupérés avec succès.',
                'data' => [
                    'projet' => new ProjetsResource($projet),
                    'tdr' => new TdrResource($projet->tdrPrefaisabilite->first()),
                    'rapport' => new RapportResource($projet->rapportPrefaisabilite()->first()),
                    // Données pour statut VALIDATION_PF
                    /* 'rapport_prefaisabilite' => $rapportPrefaisabilite ? [
                        'id' => $rapportPrefaisabilite->id,
                        'statut' => $rapportPrefaisabilite->statut,
                        'date_soumission' => $rapportPrefaisabilite->date_soumission?->format('d/m/Y H:i:s'),
                        'recommandation' => $rapportPrefaisabilite->recommandation,
                        'info_cabinet_etude' => $rapportPrefaisabilite->info_cabinet_etude,
                        'checklist_suivi' => $rapportPrefaisabilite->checklist_suivi,
                        'soumis_par_id' => $rapportPrefaisabilite->soumis_par_id,
                        'fichiers' => $rapportPrefaisabilite->fichiers?->map(function ($fichier) {
                            return [
                                'id' => $fichier->id,
                                'nom_original' => $fichier->nom_original,
                                'type' => $fichier->mime_type ?? $fichier->type,
                                'taille' => $fichier->taille,
                                'chemin' => $fichier->chemin
                            ];
                        })
                    ] : null, */
                    'evaluation_validation' => $evaluationValidation ?/*  [
                        'id' => $evaluationValidation->id,
                        'valider_le' => $evaluationValidation->valider_le?->format('d/m/Y H:i:s'),
                        'valider_par' => $evaluationValidation->valider_par,
                        'decision' => $evaluationValidation->resultats_evaluation,
                        'commentaire' => $evaluationValidation->commentaire
                    ] : null, ? */ [
                        'id' => $evaluationValidation->id,
                        'evaluation' => $evaluationValidation->evaluation,
                        'decision' => $evaluationValidation->resultats_evaluation,
                        'statut' => $evaluationValidation->statut, // 0=en cours, 1=terminée
                        'evaluateur' => new UserResource($evaluationValidation->evaluateur),
                        'date_debut' => Carbon::parse($evaluationValidation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                        'date_fin' => Carbon::parse($evaluationValidation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                        'commentaire_global' => $evaluationValidation->commentaire
                    ] : null,
                    'fichiers_validation' => FichierResource::collection($fichiersValidation),
                    'checklist_suivi_validation' => ($evaluationValidation && $evaluationValidation->evaluation && isset($evaluationValidation->evaluation["champs_evalues"])) ? $evaluationValidation->evaluation["champs_evalues"] : null
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtenir les actions possibles selon le statut et le résultat d'évaluation
     */
    private function getActionsPossibles($statut, $resultatEvaluation = null): array
    {
        return match ($statut) {
            StatutIdee::EVALUATION_TDR_PF => [
                'evaluer' => 'Procéder à l\'évaluation des TDRs',
                // Actions de décision finale seulement pour cas "non accepté"
                ...(($resultatEvaluation === 'non_accepte') ? [
                    'reviser' => 'Reviser tdr malgré l\'évaluation négative',
                    'abandonner' => 'Abandonner le projet'
                ] : [])
            ],
            StatutIdee::VALIDATION_PF => [
                'maturite' => [
                    'libelle' => 'Projet à maturité',
                    'description' => 'Valider le projet comme étant à maturité',
                    'consequence' => 'Statut MATURITE - Projet complexe de type 1'
                ],
                'faisabilite' => [
                    'libelle' => 'Étude de faisabilité',
                    'description' => 'Orienter vers une étude de faisabilité',
                    'consequence' => 'Statut TDR_FAISABILITE - Projet complexe de type 2'
                ],
                'reprendre' => [
                    'libelle' => 'Reprendre l\'étude',
                    'description' => 'Renvoyer pour reprendre l\'étude de préfaisabilité',
                    'consequence' => 'Retour au statut SOUMISSION_RAPPORT_PF'
                ],
                'abandonner' => [
                    'libelle' => 'Abandonner le projet',
                    'description' => 'Mettre fin au projet',
                    'consequence' => 'Statut ABANDON'
                ],
                'sauvegarder' => [
                    'libelle' => 'Sauvegarder',
                    'description' => 'Sauvegarder les données sans changer le statut',
                    'consequence' => 'Aucun changement de statut'
                ]
            ],
            default => []
        };
    }

    /**
     * Obtenir les actions suivantes selon le résultat d'évaluation
     */
    private function getActionsSuivantesSelonResultat(string $resultatGlobal): array
    {
        return match ($resultatGlobal) {
            'passe' => [
                'type' => 'automatique',
                'message' => 'Évaluation réussie. Le projet passera automatiquement à l\'étape de soumission du rapport.',
                'action_automatique' => 'SOUMISSION_RAPPORT_PF',
                'actions_manuelles' => []
            ],
            'retour' => [
                'type' => 'automatique',
                'message' => 'Des améliorations sont nécessaires. Le projet retournera automatiquement à l\'étape de soumission des TDRs.',
                'action_automatique' => 'R_TDR_PREFAISABILITE',
                'actions_manuelles' => []
            ],
            'non_accepte' => [
                'type' => 'decision_requise',
                'message' => 'Évaluation négative. Une décision manuelle est requise.',
                'action_automatique' => null,
                'actions_manuelles' => [
                    [
                        'action' => 'reviser',
                        'libelle' => 'Reviser tdr malgré l\'évaluation',
                        'description' => 'Permettre au projet de reviser avec de nouveaux TDRs',
                        'consequence' => 'Retour à l\'étape TDR_PREFAISABILITE'
                    ],
                    [
                        'action' => 'abandonner',
                        'libelle' => 'Abandonner le projet',
                        'description' => 'Mettre fin au projet suite à l\'évaluation négative',
                        'consequence' => 'Statut ABANDON'
                    ]
                ]
            ],
            default => [
                'type' => 'indefini',
                'message' => 'Résultat d\'évaluation non reconnu.',
                'action_automatique' => null,
                'actions_manuelles' => []
            ]
        };
    }

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function soumettreRapportPrefaisabilite($projetId, array $data): JsonResponse
    {

        try {
            DB::beginTransaction();

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            if (!auth()->user()->hasPermissionTo('soumettre-un-rapport-de-prefaisabilite') && auth()->user()->type !== 'dpaf' && auth()->user()->profilable_type !== Dpaf::class) {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::SOUMISSION_RAPPORT_PF->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de soumission du rapport de préfaisabilité.'
                ], 422);
            }

            // Déterminer si c'est une soumission ou un brouillon
            $action = $data['action'] ?? 'submit';
            $estBrouillon = $action === 'draft';

            if (!$estBrouillon) {

                if (empty(data_get($data, 'checklist_suivi_rapport_prefaisabilite'))) {
                    throw ValidationException::withMessages([
                        "checklist_suivi_rapport_prefaisabilite" => "Veuillez faire le suivi du rapport de préfaisabilité !"
                    ]);
                }

                if (!isset($data['etude_prefaisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "est_finance" => "Veuillez préciser si l'etude de préfaisabilité est financé ou pas !"
                    ]);
                }
            }

            // Récupérer le dernier rapport de préfaisabilité s'il existe
            $rapportExistant = $projet->rapportPrefaisabilite()->first();

            // Préparer les données du rapport
            $rapportData = [
                'projet_id' => $projet->id,
                'type' => 'prefaisabilite',
                'statut' => $estBrouillon ? 'brouillon' : 'soumis',
                'intitule' => 'Rapport de préfaisabilité',
                'checklist_suivi' => $data['checklist_suivi_rapport_prefaisabilite'] ?? null,
                'info_cabinet_etude' => [
                    'nom_cabinet' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                    'contact_cabinet' => $data['cabinet_etude']['contact_cabinet'] ?? null,
                    'email_cabinet' => $data['cabinet_etude']['email_cabinet'] ?? null,
                    'adresse_cabinet' => $data['cabinet_etude']['adresse_cabinet'] ?? null,
                ],
                'recommandation' => $data['recommandation'] ?? null,
                'soumis_par_id' => auth()->id()
            ];

            // Définir la date de soumission seulement si c'est une soumission finale
            if (!$estBrouillon) {
                $rapportData['date_soumission'] = now();
            }

            // Créer ou mettre à jour le rapport
            if ($rapportExistant && $rapportExistant->statut === 'brouillon') {
                // Mettre à jour le rapport existant s'il est en brouillon
                $rapport = $rapportExistant;
                $rapport->fill($rapportData);
                $rapport->save();
                $message = $estBrouillon ? 'Rapport sauvegardé en brouillon.' : 'Rapport soumis avec succès.';
            } elseif ($rapportExistant && $rapportExistant->statut === 'soumis' && !$estBrouillon) {
                // Si un rapport soumis existe déjà et qu'on soumet à nouveau, créer une nouvelle version
                $rapportData['parent_id'] = $rapportExistant->id;
                $rapport = Rapport::create($rapportData);
                $message = 'Nouvelle version du rapport soumise avec succès.';
            } else {
                // Créer un nouveau rapport (première version)
                $rapport = Rapport::create($rapportData);
                $message = $estBrouillon ? 'Rapport sauvegardé en brouillon.' : 'Rapport soumis avec succès.';
            }

            // Traiter les checklists (pour brouillons et soumissions)
            $resultChecklistValidation = null;

            // Traiter la checklist de contrôle d'adaptation si projet à haut risque
            if ($projet->est_a_haut_risque) {
                if (!$estBrouillon && (!isset($data['checklist_controle_adaptation_haut_risque']) || $data['checklist_controle_adaptation_haut_risque'] == null)) {
                    throw new Exception("Il faut faire le suivi des mesures d'adaptation", 1);
                }

                if (isset($data['checklist_controle_adaptation_haut_risque']) && $data['checklist_controle_adaptation_haut_risque']) {
                    // Log pour debug
                    \Log::info('Traitement checklist adaptation', [
                        'projet_id' => $projet->id,
                        'est_a_haut_risque' => $projet->est_a_haut_risque,
                        'checklist_presente' => isset($data['checklist_controle_adaptation_haut_risque']),
                        'est_brouillon' => $estBrouillon
                    ]);

                    $this->traiterChecklistControleAdaptation(
                        $projet,
                        $data['checklist_controle_adaptation_haut_risque'],
                        $estBrouillon
                    );
                } else {
                    // Log pour debug quand la checklist n'est pas traitée
                    \Log::warning('Checklist adaptation non traitée', [
                        'projet_id' => $projet->id,
                        'est_a_haut_risque' => $projet->est_a_haut_risque,
                        'checklist_presente' => isset($data['checklist_controle_adaptation_haut_risque']),
                        'checklist_non_vide' => !empty($data['checklist_controle_adaptation_haut_risque']),
                        'data_keys' => array_keys($data)
                    ]);
                }
            }

            // Traiter la checklist de suivi pour la soumission finale
            if (isset($data['checklist_suivi_rapport_prefaisabilite'])) {
                // Préparer les fichiers et données pour la soumission finale
                $fichiersData = [
                    'rapport' => $data['rapport'] ?? null,
                    'proces_verbal' => $data['proces_verbal'] ?? null,
                    'cabinet_etude' => $data['cabinet_etude'] ?? null,
                    'recommandation' => $data['recommandation'] ?? null
                ];

                $resultChecklistSuivi = $this->traiterChecklistSuiviRapportPrefaisabilite(
                    $rapport,
                    $data['checklist_suivi_rapport_prefaisabilite'],
                    false,
                    $fichiersData
                );

                if (!$resultChecklistSuivi['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $resultChecklistSuivi['message']
                    ], 422);
                }
            }

            // Changer le statut du projet seulement pour les soumissions finales
            if (!$estBrouillon) {

                // Traitement et sauvegarde du fichier rapport
                $fichierRapport = null;
                if (isset($data['rapport'])) {
                    $fichierRapport = $this->gererFichierRapport($rapport, $data['rapport'], $data);
                }

                // Traitement et sauvegarde du procès verbal
                $fichierProcesVerbal = null;
                if (isset($data['proces_verbal'])) {
                    $fichierProcesVerbal = $this->gererFichierProcesVerbal($rapport, $data['proces_verbal'], $data);
                }

                //validation des informations de si l'étude de préfaisabilité est financée
                if (!isset($data['etude_prefaisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' est obligatoire."
                    ]);
                }
                /*
                $info_etude_prefaisabilite = $projet->info_etude_prefaisabilite ?? [];

                // on doit valider si c'est une valeur booléenne
                // par exemple une chaîne de caractères, un entier, un tableau, etc.
                // mais si la valeur est 0 ou 1, on peut la considérer comme booléenne

                if (is_string($data['etude_prefaisabilite']['est_finance'])) {
                    $valeur = strtolower($data['etude_prefaisabilite']['est_finance']);
                    if ($valeur === 'true' || $valeur === '1') {
                        $data['etude_prefaisabilite']['est_finance'] = true;
                    } elseif ($valeur === 'false' || $valeur === '0') {
                        $data['etude_prefaisabilite']['est_finance'] = false;
                    } else {
                        throw ValidationException::withMessages([
                            "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                        ]);
                    }
                } elseif (is_int($data['etude_prefaisabilite']['est_finance'])) {
                    if ($data['etude_prefaisabilite']['est_finance'] === 1) {
                        $data['etude_prefaisabilite']['est_finance'] = true;
                    } elseif ($data['etude_prefaisabilite']['est_finance'] === 0) {
                        $data['etude_prefaisabilite']['est_finance'] = false;
                    } else {
                        throw ValidationException::withMessages([
                            "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                        ]);
                    }
                } elseif (is_array($data['etude_prefaisabilite']['est_finance']) || is_null($data['etude_prefaisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                    ]);
                } else {
                    // Si c'est déjà une valeur booléenne, ne rien faire
                }

                if (!is_bool($data['etude_prefaisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                    ]);
                }

                $est_finance = $data['etude_prefaisabilite']['est_finance'] ?? ($info_etude_prefaisabilite['est_finance'] ?? false);*/

                // Mettre à jour les informations de l'étude de préfaisabilité dans le projet
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
                    "Rapport ID: {$rapport->id} soumis par cabinet: " . ($rapport->info_cabinet_etude['nom_cabinet'] ?? 'N/A'),
                    auth()->user()->personne->id
                );

                // Envoyer une notification
                $this->envoyerNotificationSoumissionRapport($projet, $rapport, $fichierRapport);
            }

            if (isset($data['etude_prefaisabilite']['est_finance'])) {

                $info_etude_prefaisabilite = $projet->info_etude_prefaisabilite ?? [];

                // on doit valider si c'est une valeur booléenne
                // par exemple une chaîne de caractères, un entier, un tableau, etc.
                // mais si la valeur est 0 ou 1, on peut la considérer comme booléenne

                if (is_string($data['etude_prefaisabilite']['est_finance'])) {
                    $valeur = strtolower($data['etude_prefaisabilite']['est_finance']);
                    if ($valeur === 'true' || $valeur === '1') {
                        $data['etude_prefaisabilite']['est_finance'] = true;
                    } elseif ($valeur === 'false' || $valeur === '0') {
                        $data['etude_prefaisabilite']['est_finance'] = false;
                    } else {
                        throw ValidationException::withMessages([
                            "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                        ]);
                    }
                } elseif (is_int($data['etude_prefaisabilite']['est_finance'])) {
                    if ($data['etude_prefaisabilite']['est_finance'] === 1) {
                        $data['etude_prefaisabilite']['est_finance'] = true;
                    } elseif ($data['etude_prefaisabilite']['est_finance'] === 0) {
                        $data['etude_prefaisabilite']['est_finance'] = false;
                    } else {
                        throw ValidationException::withMessages([
                            "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                        ]);
                    }
                } elseif (is_array($data['etude_prefaisabilite']['est_finance']) || is_null($data['etude_prefaisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                    ]);
                }

                if (!is_bool($data['etude_prefaisabilite']['est_finance'])) {
                    throw ValidationException::withMessages([
                        "etude_prefaisabilite.est_finance" => "Le champ 'est_finance' doit être une valeur booléenne."
                    ]);
                }

                $est_finance = $data['etude_prefaisabilite']['est_finance'] ?? ($info_etude_prefaisabilite['est_finance'] ?? false);

                // Mettre à jour les informations de l'étude de préfaisabilité dans le projet
                $projet->update([
                    // Fusionner avec les nouvelles valeurs provenant de $data
                    'info_etude_prefaisabilite' => array_merge($info_etude_prefaisabilite, [
                        'date_demande'   => $data['etude_prefaisabilite']['date_demande'] ?? ($info_etude_prefaisabilite['date_demande'] ?? null),
                        'date_obtention' => $data['etude_prefaisabilite']['date_obtention'] ?? ($info_etude_prefaisabilite['date_obtention'] ?? null),
                        'montant'        => $data['etude_prefaisabilite']['montant'] ?? ($info_etude_prefaisabilite['montant'] ?? null),
                        'reference'      => $data['etude_prefaisabilite']['reference'] ?? ($info_etude_prefaisabilite['reference'] ?? null),
                        'est_finance'    => $est_finance,
                    ])
                ]);
            }

            DB::commit();

            // Charger les relations nécessaires pour le resource
            $rapport->load(['fichiers', 'soumisPar', 'projet']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'rapport_id' => $rapport->id,
                    'projet_id' => $projet->id,
                    'statut_rapport' => $rapport->statut,
                    'statut_projet' => $projet->statut->value,
                    'action' => $estBrouillon ? 'draft' : 'submit',
                    'rapport' => new RapportResource($rapport)
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer le rapport soumis pour un projet
     */
    public function getDetailsSoumissionRapportPrefaisabilite(int $projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier les permissions d'accès
            /* if (auth()->user()->profilable?->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== \App\Models\Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'accès pour effectuer cette action", 403);
            } */

            // Récupérer le rapport soumis le plus récent
            $rapport = \App\Models\Rapport::where('projet_id', $projetId)
                ->where('type', 'prefaisabilite')
                ->with(['fichiersRapport', 'procesVerbaux', 'soumisPar', 'projet', 'champs', 'documentsAnnexes'])
                ->latest('created_at')
                ->first();

            if (!$rapport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun rapport soumis trouvé pour ce projet.',
                    'data' => null
                ], 206);
            }

            return response()->json([
                'success' => true,
                'data' => new \App\Http\Resources\RapportResource($rapport),
                'message' => 'Détails de soumission du rapport de préfaisabilité récupérés avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rapport soumis: ' . $e->getMessage(),
                'data' => null
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }

    /**
     * Gérer le fichier TDR avec versioning intelligent
     */
    private function gererFichierTdr(Projet $projet, $fichier, array $data): ?Fichier
    {
        // Calculer le hash du nouveau fichier
        $nouveauHash = md5_file($fichier->getRealPath());

        // Vérifier s'il y a déjà un TDR actif avec le même hash
        $tdrIdentique = $projet->tdrs_prefaisabilite()
            ->where('hash_md5', $nouveauHash)
            ->where('is_active', true)
            ->first();

        if ($tdrIdentique) {
            return $tdrIdentique;
        }

        // Pour les TDRs, toujours vérifier le statut R_TDR_PREFAISABILITE
        $doitCreerNouvelleVersion = ($projet->statut->value === StatutIdee::R_TDR_PREFAISABILITE->value);

        if ($doitCreerNouvelleVersion) {
            return $this->creerNouvelleVersionTdr($projet, $fichier, $data);
        } else {
            return $this->remplacerTdrExistant($projet, $fichier, $data);
        }
    }

    /**
     * Créer une nouvelle version du TDR
     */
    private function creerNouvelleVersionTdr(Projet $projet, $fichier, array $data): Fichier
    {
        // Récupérer la dernière version
        $derniereVersion = $projet->tdrs_prefaisabilite()
            ->orderBy('created_at', 'desc')
            ->first();

        $nouvelleVersion = 1;
        if ($derniereVersion) {
            $versionActuelle = $derniereVersion->metadata['version'] ?? 1;
            $nouvelleVersion = $versionActuelle + 1;

            // Archiver l'ancienne version
            $derniereVersion->update([
                'is_active' => false,
                'metadata' => array_merge($derniereVersion->metadata ?? [], [
                    'statut' => 'archive',
                    'archive_le' => now(),
                    'remplace_par_version' => $nouvelleVersion
                ])
            ]);
        }

        return $this->sauvegarderFichierTdr($projet, $fichier, $data, $nouvelleVersion);
    }

    /**
     * Remplacer le TDR existant (même cycle)
     */
    private function remplacerTdrExistant(Projet $projet, $fichier, array $data): Fichier
    {
        $tdrExistant = $projet->tdrs_prefaisabilite()->where('is_active', true)->first();
        $version = 1;

        if ($tdrExistant) {
            $version = $tdrExistant->metadata['version'] ?? 1;
            // Supprimer l'ancien fichier physique
            Storage::disk('public')->delete($tdrExistant->chemin);
            $tdrExistant->delete();
        }

        return $this->sauvegarderFichierTdr($projet, $fichier, $data, $version);
    }

    /**
     * Sauvegarder le fichier TDR avec version
     */
    private function sauvegarderFichierTdr(\App\Models\Tdr $tdr, $fichier, array $data, int $version = 1): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        // Créer ou récupérer la structure de dossiers pour TDR
        $dossierTdr = $this->getOrCreateTdrFolderStructure($tdr->projet_id, 'tdr');

        // Hasher l'identifiant BIP pour le stockage physique
        $hashedIdentifiantBip = hash('sha256', $tdr->projet->identifiant_bip);

        // Générer un nom de fichier unique avec timestamp
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $nomOriginal;

        // Créer le chemin basé sur la structure de dossiers en base de données (avec hash pour stockage)
        $cheminStockage = $dossierTdr ?
            $dossierTdr->full_path :
            'projets/' . $hashedIdentifiantBip . '/Evaluation ex-ante/Etude de préfaisabilité/Termes de référence/Documents TDR';

        // Nettoyer le chemin pour le stockage physique (éliminer espaces et caractères spéciaux)
        $cheminStockagePhysique = strtolower(SlugHelper::generateFilePath($cheminStockage));

        // Créer le dossier s'il n'existe pas
        \Storage::disk('local')->makeDirectory($cheminStockagePhysique);

        $chemin = $fichier->storeAs($cheminStockagePhysique, $nomStockage, 'local');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => $data['resume'] ?? "Termes de référence pour l'étude de préfaisabilité",
            'commentaire' => $data['resume'] ?? null,
            'metadata' => [
                'type_document' => 'tdr-prefaisabilite',
                'tdr_id' => $tdr->id,
                'projet_id' => $tdr->projet_id,
                'version' => $version,
                'statut' => 'actif',
                'resume' => $data['resume'] ?? null,
                'tdr_faisabilite' => $data['tdr_faisabilite'] ?? null,
                'tdr_pre_faisabilite' => $data['tdr_pre_faisabilite'] ?? null,
                'type_tdr' => $data['type_tdr'] ?? 'pre_faisabilite',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()->toISOString(),
                'uploaded_context' => 'tdr-prefaisabilite',
                'dossier_public' => $dossierTdr ? $dossierTdr->full_path : 'Projets/' . $tdr->projet->identifiant_bip . '/Evaluation ex-ante/Etude de préfaisabilité/Termes de référence'

            ],
            'dossier_id' => $dossierTdr?->id,
            'fichier_attachable_id' => $tdr->id,
            'fichier_attachable_type' => \App\Models\Tdr::class,
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
    private function creerEvaluationTdr(Tdr $tdr, array $data)
    {
        // Récupérer une évaluation en cours existante ou en créer une nouvelle pour ce TDR
        $evaluationEnCours = $tdr->evaluationEnCours();

        if (!$evaluationEnCours) {
            // Récupérer l'évaluation parent si c'est une ré-évaluation
            $evaluationParent = $tdr->evaluationParent();

            // Vérifier si une évaluation est déjà terminée (sauf pour les resoumissions)
            $evaluationTerminee = $tdr->evaluationPrefaisabiliteTerminer();
            if ($evaluationTerminee && !$tdr->parent_id) {
                throw new \Exception('Une évaluation a déjà été terminée pour ce TDR. Impossible de créer une nouvelle évaluation.', 403);
            }

            // Créer la nouvelle évaluation
            $evaluationData = [
                'type_evaluation' => 'tdr-prefaisabilite',
                'evaluateur_id' => auth()->id(),
                'evaluation' => [],
                'resultats_evaluation' => [],
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => isset($data['evaluer']) && $data['evaluer'] ? now() : null,
                'statut' => isset($data['evaluer']) && $data['evaluer'] ? 1 : 0, // En cours ou finalisé
                'id_evaluation' => $evaluationParent ? $evaluationParent->id : null // Lien vers le parent
            ];

            $evaluationEnCours = $tdr->evaluations()->create($evaluationData);
        } else {
            // evaluer l'évaluation si demandé
            if (isset($data['evaluer']) && $data['evaluer']) {
                $evaluationEnCours->fill([
                    'date_fin_evaluation' => now(),
                    'statut' => 1
                ]);
                $evaluationEnCours->save();
            }
        }

        // Enregistrer les appréciations pour chaque champ
        if (isset($data['evaluations_champs'])) {

            $syncData = [];

            foreach ($data['evaluations_champs'] as $evaluationChamp) {
                $syncData[$evaluationChamp['champ_id']] = [
                    'note' => $evaluationChamp['appreciation'],
                    'date_note' => now(),
                    'commentaires' => $evaluationChamp['commentaire'] ?? null,
                ];
            }

            $evaluationEnCours->champs_evalue()->syncWithoutDetaching($syncData);
        }

        // Enregistrer le commentaire global si fourni
        if (isset($data['commentaire'])) {
            $evaluationEnCours->fill(['commentaire' => $data['commentaire']]);
            $evaluationEnCours->save();
        }

        // Forcer la finalisation de l'évaluation lors de l'enregistrement dans evaluerTdrs
        $evaluationEnCours->update([
            'date_fin_evaluation' => isset($data['evaluer']) && $data['evaluer'] ? now() : null,
            'statut' => isset($data['evaluer']) && $data['evaluer'] ? 1 : 0, // En cours ou finalisé
        ]);

        $evaluationEnCours->refresh();

        if ($data["evaluer"]) {
            $tdr->canevas_appreciation_tdr = (new CanevasAppreciationTdrResource($this->documentRepository->getCanevasAppreciationTdrPrefaisabilite()))->toArray(request());
            $tdr->save();
        }

        return $evaluationEnCours;
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
     * Traiter la décision d'évaluation automatiquement selon les règles SFD-011
     */
    private function traiterDecisionEvaluationTdrAutomatique(Projet $projet, array $resultats, null|Tdr $tdr, $evaluation = null): StatutIdee
    {
        switch ($resultats['resultat_global']) {
            case 'passe':
                // La présélection a été un succès → SoumissionRapportPF (automatique)
                $projet->update([
                    'statut' => StatutIdee::SOUMISSION_RAPPORT_PF,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_PF),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::SOUMISSION_RAPPORT_PF)
                ]);

                $tdr->update([
                    'decision_validation' => 'valider',
                    'commentaire_validation' => $resultats["message_resultat"],
                    'statut' => 'valide'
                ]);

                return StatutIdee::SOUMISSION_RAPPORT_PF;

            case 'retour':
                // Créer un nouveau TDR en brouillon pour la révision
                $tdr->refresh();
                $newTdr = $tdr->replicate();

                $newTdr->statut = 'brouillon';
                $newTdr->decision_validation = null;
                $newTdr->accept_term = false;
                $newTdr->parent_id = $tdr->id;
                $newTdr->date_validation = null;
                $newTdr->date_soumission = null;
                $newTdr->projet_id = $tdr->projet->id;
                $newTdr->rediger_par_id =  $tdr->redigerPar->id;
                $newTdr->created_at = now();
                $newTdr->updated_at = null;

                // Copier les canevas de la note originale vers la nouvelle note
                $newTdr->canevas_appreciation_tdr = $tdr->canevas_appreciation_tdr;
                $newTdr->save();

                // NOTE: L'évaluation sera créée automatiquement lors de la resoumission du TDR corrigé
                // La logique de duplication de l'évaluation (champs passés, etc.) sera appliquée à ce moment

                // Retour pour travail supplémentaire → R_TDR_Préfaisabilité (automatique)
                $projet->update([
                    'statut' => StatutIdee::R_TDR_PREFAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::R_TDR_PREFAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::R_TDR_PREFAISABILITE)
                ]);

                $tdr->update([
                    'decision_validation' => 'reviser',
                    'commentaire_validation' => $resultats["message_resultat"],
                ]);

                return StatutIdee::R_TDR_PREFAISABILITE;
            case 'non_accepte':
                // Créer un nouveau TDR en brouillon pour refaire complètement
                $tdr->refresh();
                $newTdr = $tdr->replicate();

                $newTdr->statut = 'brouillon';
                $newTdr->decision_validation = null;
                $newTdr->accept_term = false;
                $newTdr->parent_id = $tdr->id;
                $newTdr->date_validation = null;
                $newTdr->date_soumission = null;
                $newTdr->projet_id = $tdr->projet->id;
                $newTdr->rediger_par_id = $tdr->redigerPar->id;
                $newTdr->created_at = now();
                $newTdr->updated_at = null;

                // Copier les canevas de la note originale vers la nouvelle note
                $newTdr->canevas_appreciation_tdr = $tdr->canevas_appreciation_tdr;
                $newTdr->save();

                // NOTE: L'évaluation sera créée automatiquement lors de la resoumission du TDR corrigé
                // La logique de duplication de l'évaluation (champs passés, etc.) sera appliquée à ce moment

                // Non accepté → TDR_Préfaisabilité (automatique, révision directe)
                $projet->update([
                    'statut' => StatutIdee::TDR_PREFAISABILITE,
                    'phase' => $this->getPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE),
                    'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::TDR_PREFAISABILITE)
                ]);

                $tdr->update([
                    'decision_validation' => 'reviser',
                    'commentaire_validation' => $resultats["message_resultat"],
                ]);

                return StatutIdee::TDR_PREFAISABILITE;

            default:
                return StatutIdee::EVALUATION_TDR_PF;
        }
    }

    /**
     * Créer une nouvelle évaluation basée sur une évaluation parent (pour TDR retourné)
     * Copie les champs "passés" et réinitialise les champs "retour"/"non_accepte"
     */
    private function creerEvaluationPourTdrResoumis(Tdr $nouveauTdr, Tdr $ancienTdr): void
    {
        // Récupérer l'évaluation terminée du TDR parent
        $evaluationTerminee = $ancienTdr->evaluationPrefaisabiliteTerminer();

        if (!$evaluationTerminee) {
            return; // Pas d'évaluation à dupliquer
        }

        // Créer une nouvelle évaluation liée au nouveau TDR
        $newEvaluation = $evaluationTerminee->replicate();
        $newEvaluation->projetable_id = $nouveauTdr->id;
        $newEvaluation->projetable_type = get_class($nouveauTdr);
        $newEvaluation->id_evaluation = $evaluationTerminee->id; // Lien vers l'évaluation parent
        $newEvaluation->canevas = $evaluationTerminee->canevas; // Copier le canevas
        $newEvaluation->statut = 0; // En cours
        $newEvaluation->date_debut_evaluation = now();
        $newEvaluation->date_fin_evaluation = null;
        $newEvaluation->valider_le = null;
        $newEvaluation->valider_par = null;
        $newEvaluation->resultats_evaluation = [];

        // Sauvegarder d'abord la nouvelle évaluation avec des valeurs temporaires
        $newEvaluation->evaluation = [];
        $newEvaluation->resultats_evaluation = [];
        $newEvaluation->created_at = now();
        $newEvaluation->updated_at = null;
        $newEvaluation->save();

        // Copier les relations champs_evalue de l'ancienne évaluation
        // Pour les champs "passé" : copier tel quel
        // Pour les autres (retour/non_accepte) : mettre null pour forcer la réévaluation
        $champsEvalues = $evaluationTerminee->champs_evalue;
        foreach ($champsEvalues as $champ) {
            $note = $champ->pivot->note;

            if ($note === 'passe') {
                // Si passé, copier tel quel
                $newEvaluation->champs_evalue()->attach($champ->id, [
                    'note' => $note,
                    'date_note' => $champ->pivot->date_note,
                    'commentaires' => $champ->pivot->commentaires,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // Si retour ou non_accepte, mettre null (pas de copie dans pivot)
                // Les anciennes valeurs seront dans le JSON evaluation avec le suffixe "_passer"
                dump($champ);
            }
        }

        // Recharger pour avoir accès aux relations
        $newEvaluation->refresh();

        // Construire le tableau des évaluations de champs pour le calcul
        $evaluationsChamps = collect($this->documentRepository->getCanevasAppreciationTdrPrefaisabilite()->all_champs)->map(function ($champ) use ($newEvaluation) {
            $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);

            return [
                'champ_id' => $champ['id'],
                'label' => $champ['label'],
                'attribut' => $champ['attribut'],
                'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
            ];
        })->toArray();

        // Construire le JSON evaluation basé sur les champs copiés
        $resultatsExamen = $this->calculerResultatEvaluationTdr($newEvaluation, ['evaluations_champs' => $evaluationsChamps]);

        // Récupérer l'ancienne évaluation pour référence
        $ancienneEvaluation = $evaluationTerminee->evaluation ?? [];
        $anciensChampsEvalues = collect($ancienneEvaluation['champs_evalues'] ?? []);

        $evaluationComplete = [
            'champs_evalues' => collect($this->documentRepository->getCanevasAppreciationTdrPrefaisabilite()->all_champs)->map(function ($champ) use ($newEvaluation, $anciensChampsEvalues) {
                $champEvalue = collect($newEvaluation->champs_evalue)->firstWhere('attribut', $champ['attribut']);
                $ancienChampEvalue = $anciensChampsEvalues->firstWhere('attribut', $champ['attribut']);

                dd($anciensChampsEvalues->toArray());
                $result = [
                    'champ_id' => $champ['id'],
                    'label' => $champ['label'],
                    'attribut' => $champ['attribut'],
                    'ordre_affichage' => $champ['ordre_affichage'],
                    'type_champ' => $champ['type_champ'],
                    'appreciation' => $champEvalue ? $champEvalue['pivot']['note'] : null,
                    'commentaire_evaluateur' => $champEvalue ? $champEvalue['pivot']['commentaires'] : null,
                    'date_appreciation' => $champEvalue ? $champEvalue['pivot']['date_note'] : null,
                ];

                // Si le champ n'est pas dans la nouvelle évaluation mais existe dans l'ancienne
                // C'est un champ qui n'était pas "passé", on ajoute les anciennes valeurs avec "_passer"
                if (!$champEvalue && $ancienChampEvalue) {
                    $result['appreciation_passer'] = $ancienChampEvalue['appreciation'] ?? null;
                    $result['commentaire_passer_evaluateur'] = $ancienChampEvalue['commentaire_evaluateur'] ?? null;
                    $result['date_appreciation_passer'] = $ancienChampEvalue['date_appreciation'] ?? null;
                }

                return $result;
            })->toArray(),
            'statistiques' => $resultatsExamen
        ];

        // Mettre à jour avec les données complètes
        $newEvaluation->evaluation = $evaluationComplete;
        $newEvaluation->resultats_evaluation = $resultatsExamen;
        $newEvaluation->save();
    }

    /**
     * Gérer le fichier rapport avec versioning intelligent
     */
    private function gererFichierRapport(Rapport $rapport, $fichier, array $data): ?Fichier
    {
        // Calculer le hash du nouveau fichier
        $nouveauHash = md5_file($fichier->getRealPath());

        // Vérifier s'il y a déjà un fichier rapport avec le même hash lié à ce rapport
        $fichierIdentique = $rapport->fichiersRapport()
            ->where('hash_md5', $nouveauHash)
            ->where('is_active', true)
            ->first();

        if ($fichierIdentique) {
            return $fichierIdentique;
        }

        // Désactiver les anciens fichiers rapport de ce rapport
        $rapport->fichiersRapport()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Hasher les IDs pour le chemin selon le pattern projets/{hash_projet_id}/etude_de_prefaisabilite/rapport/{hash_id}
        $hashedProjectId = hash('sha256', $rapport->projet_id);
        $hashedRapportId = hash('sha256', $rapport->id);

        // Stocker le fichier sur le disque
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $nomOriginal;
        $chemin = $fichier->storeAs("projets/{$hashedProjectId}/evaluation_ex_ante/etude_de_prefaisabilite/rapport_prefaisabilite/{$hashedRapportId}", $nomStockage, 'local');

        // Créer le nouveau fichier et l'associer au rapport
        $fichierCree = Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => $nouveauHash,
            'description' => 'Rapport de préfaisabilité',
            'commentaire' => $data['commentaire_rapport'] ?? null,
            'categorie' => 'rapport',
            'is_active' => true,
            'uploaded_by' => auth()->id(),
            'metadata' => [
                'type_document' => 'rapport-prefaisabilite',
                'rapport_id' => $rapport->id,
                'projet_id' => $rapport->projet_id,
                'statut' => 'actif',
                'soumis_par' => auth()->id(),
                'soumis_le' => now(),
                'folder_structure' => "projets/{$hashedProjectId}/evaluation_ex_ante/etude_de_prefaisabilite/rapport_prefaisabilite/{$hashedRapportId}"
            ],
            'fichier_attachable_type' => Rapport::class,
            'fichier_attachable_id' => $rapport->id
        ]);

        return $fichierCree;
    }

    /**
     * Gérer le fichier procès verbal avec versioning intelligent
     */
    private function gererFichierProcesVerbal(Rapport $rapport, $fichier, array $data): ?Fichier
    {
        // Calculer le hash du nouveau fichier
        $nouveauHash = md5_file($fichier->getRealPath());

        // Vérifier s'il y a déjà un procès verbal avec le même hash lié à ce rapport
        $procesVerbalIdentique = $rapport->procesVerbaux()
            ->where('hash_md5', $nouveauHash)
            ->where('is_active', true)
            ->first();

        if ($procesVerbalIdentique) {
            return $procesVerbalIdentique;
        }

        // Désactiver les anciens procès verbaux de ce rapport
        $rapport->procesVerbaux()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Hasher les IDs pour le chemin selon le pattern projets/{hash_projet_id}/etude_de_prefaisabilite/rapport/{hash_id}
        $hashedProjectId = hash('sha256', $rapport->projet_id);
        $hashedRapportId = hash('sha256', $rapport->id);

        // Stocker le fichier sur le disque
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $nomOriginal;
        $chemin = $fichier->storeAs("projets/{$hashedProjectId}/evaluation_ex_ante/etude_de_prefaisabilite/rapport_prefaisabilite/{$hashedRapportId}", $nomStockage, 'local');

        // Créer le nouveau fichier et l'associer au rapport
        $fichierCree = Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => $nouveauHash,
            'description' => 'Procès-verbal de préfaisabilité',
            'commentaire' => $data['commentaire_proces_verbal'] ?? null,
            'categorie' => 'proces-verbal',
            'is_active' => true,
            'uploaded_by' => auth()->id(),
            'metadata' => [
                'type_document' => 'proces-verbal-prefaisabilite',
                'rapport_id' => $rapport->id,
                'projet_id' => $rapport->projet_id,
                'statut' => 'actif',
                'soumis_par' => auth()->id(),
                'soumis_le' => now(),
                'folder_structure' => "projets/{$hashedProjectId}/evaluation_ex_ante/etude_de_prefaisabilite/rapport_prefaisabilite/{$hashedRapportId}"
            ],
            'fichier_attachable_type' => Rapport::class,
            'fichier_attachable_id' => $rapport->id
        ]);

        return $fichierCree;
    }

    /**
     * Sauvegarder le fichier rapport de préfaisabilité avec version
     */
    private function sauvegarderFichierRapport(Projet $projet, $fichier, array $data, int $version = 1): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = "rapport_prefaisabilite_v{$version}.{$extension}";
        $chemin = $fichier->storeAs("projets/{$projet->id}/evaluation_ex_ante/prefaisabilite", $nomStockage, 'public');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => 'Rapport d\'étude de préfaisabilité - Cabinet: ' . ($data['cabinet_etude']['nom_cabinet'] ?? 'N/A'),
            'commentaire' => $data['recommandation'] ?? null,
            'metadata' => [
                'type_document' => 'rapport-prefaisabilite',
                'projet_id' => $projet->id,
                'version' => $version,
                'statut' => 'actif',
                'cabinet' => [
                    'nom' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                    'contact' => $data['cabinet_etude']['contact_cabinet'] ?? null,
                    'email' => $data['cabinet_etude']['email_cabinet'] ?? null,
                    'adresse' => $data['cabinet_etude']['adresse_cabinet'] ?? null
                ],
                'recommandation_adaptation' => $data['recommandation'] ?? null,
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
     * Sauvegarder le fichier procès verbal avec version
     */
    private function sauvegarderFichierProcesVerbal(Projet $projet, $fichier, array $data, int $version = 1): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = "proces_verbal_prefaisabilite_v{$version}.{$extension}";
        $chemin = $fichier->storeAs("projets/{$projet->id}/prefaisabilite", $nomStockage, 'public');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => 'Procès verbal de préfaisabilité',
            'commentaire' => $data['commentaire_proces_verbal'] ?? null,
            'metadata' => [
                'type_document' => 'proces-verbal-prefaisabilite',
                'projet_id' => $projet->id,
                'version' => $version,
                'statut' => 'actif',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()
            ],
            'fichier_attachable_id' => $projet->id,
            'fichier_attachable_type' => Projet::class,
            'categorie' => 'proces-verbal-prefaisabilite',
            'ordre' => 2,
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
                'nom' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                'contact' => $data['cabinet_etude']['contact_cabinet'] ?? null,
                'email' => $data['cabinet_etude']['email_cabinet'] ?? null,
                'adresse_cabinet' => $data['cabinet_etude']['adresse_cabinet'] ?? null
            ],
            'recommandation_adaptation' => $data['recommandation_adaptation'] ?? null,
            'date_soumission_rapport' => now(),
            'soumis_par' => auth()->id()
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);
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
        return Decision::create([
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
            StatutIdee::TDR_FAISABILITE => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::MATURITE => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::RAPPORT => \App\Enums\PhasesIdee::evaluation_ex_tante,
            StatutIdee::PRET => \App\Enums\PhasesIdee::selection,
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
            StatutIdee::MATURITE => \App\Enums\SousPhaseIdee::redaction_rapport_evaluation_ex_ante,
            StatutIdee::RAPPORT => \App\Enums\SousPhaseIdee::redaction_rapport_evaluation_ex_ante,
            StatutIdee::ABANDON => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
            StatutIdee::EVALUATION_TDR_F => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::TDR_FAISABILITE => \App\Enums\SousPhaseIdee::faisabilite,
            StatutIdee::PRET => \App\Enums\SousPhaseIdee::selection,
            default => \App\Enums\SousPhaseIdee::etude_de_prefaisabilite,
        };
    }

    // Méthodes utilitaires (à implémenter selon les besoins)
    private function envoyerNotificationSoumission($projet, $fichier)
    { /* À implémenter */
    }
    private function envoyerNotificationEvaluation($projet, array $resultats)
    { /* À implémenter */
    }
    private function envoyerNotificationSoumissionRapport($projet, $rapport, $fichier = null)
    { /* À implémenter */
    }
    private function envoyerNotificationValidation($projet, string $action, array $data)
    { /* À implémenter */
    }

    /**
     * Traiter les informations pour un projet à haut risque
     */
    private function traiterProjetHautRisque(Projet $projet, array $data): void
    {
        if (isset($data['checklist_haut_risque'])) {
            // Récupérer les métadonnées existantes ou créer un nouveau tableau
            $metadata = $projet->metadata ?? [];

            // Ajouter les informations de haut risque
            $metadata['haut_risque'] = [
                'est_a_haut_risque' => true,
                'checklist_validee' => $data['checklist_haut_risque'],
                'date_validation_checklist' => now(),
                'valide_par' => auth()->id()
            ];

            // Mettre à jour le projet
            $projet->update(['metadata' => $metadata]);
        }
    }

    /**
     * Sauvegarder les données de validation sans changer le statut
     */
    private function sauvegarderDonneesValidation(Projet $projet, array $data): void
    {
        // Récupérer les métadonnées existantes ou créer un nouveau tableau
        $metadata = $projet->metadata ?? [];

        // Ajouter les informations de validation temporaires
        $metadata['validation_prefaisabilite_temp'] = [
            'est_a_haut_risque' => $data['est_a_haut_risque'] ?? false,
            'commentaire' => $data['commentaire'] ?? null,
            'checklist_haut_risque' => $data['checklist_haut_risque'] ?? null,
            'date_sauvegarde' => now(),
            'sauvegarde_par' => auth()->id()
        ];

        // Mettre à jour le projet
        $projet->update(['metadata' => $metadata]);
    }

    /**
     * Traiter la checklist de contrôle des adaptations pour projets à haut risque
     */
    private function traiterChecklistControleAdaptation($projet, array $checklistData, bool $estBrouillon = false): array
    {
        try {
            // La validation des critères et mesures est déjà faite dans le FormRequest
            // Ici, on ne fait que le traitement métier

            // Charger les relations nécessaires
            $projet->load('secteur.parent');

            $criteresCompletes = 0;
            $totalCriteres = count($checklistData['criteres']);

            // Récupérer tous les critères et mesures nécessaires avec leurs détails
            $criteresIds = collect($checklistData['criteres'])->pluck('critere_id')->unique();
            $criteres = \App\Models\Critere::whereIn('id', $criteresIds)
                ->with(['categorie_critere'])
                ->get()
                ->keyBy('id');

            $allMesuresIds = collect($checklistData['criteres'])
                ->flatMap(fn($critere) => $critere['mesures_selectionnees'] ?? [])
                ->unique();
            $mesures = \App\Models\Notation::whereIn('id', $allMesuresIds)
                ->with(['critere', 'secteur'])
                ->get()
                ->keyBy('id');

            // Récupérer les détails du secteur principal
            $secteurPrincipal = $projet->secteur->parent;
            $secteurDetails = [
                'id' => $secteurPrincipal->id,
                'nom' => $secteurPrincipal->nom
            ];

            // Traiter chaque critère de la checklist
            $criteresFormates = [];
            foreach ($checklistData['criteres'] as $critere) {
                $critereId = $critere['critere_id'];
                $mesuresSelectionnees = $critere['mesures_selectionnees'] ?? [];
                $critereDetail = $criteres->get($critereId);

                // Pour les brouillons, on peut avoir des critères sans mesures
                if (empty($mesuresSelectionnees) && $estBrouillon) {
                    continue; // Passer au critère suivant pour les brouillons
                }

                if (!empty($mesuresSelectionnees)) {
                    // Formater les mesures sélectionnées avec leurs détails
                    $mesuresFormatees = [];
                    foreach ($mesuresSelectionnees as $mesureId) {
                        $mesureDetail = $mesures->get($mesureId);
                        $mesuresFormatees[] = [
                            'id' => $mesureDetail->id,
                            'libelle' => $mesureDetail->libelle,
                            'valeur' => $mesureDetail->valeur,
                            'commentaire' => $mesureDetail->commentaire
                        ];
                    }

                    // Ajouter le critère formaté
                    $criteresFormates[] = [
                        'id' => $critereDetail->id,
                        'intitule' => $critereDetail->intitule,
                        'ponderation' => $critereDetail->ponderation,
                        'commentaire' => $critereDetail->commentaire,
                        'secteur' => array_merge($secteurDetails, ['mesures_selectionnees' => $mesuresFormatees]),

                    ];

                    $criteresCompletes++;
                }
            }

            // Enregistrer la validation de la checklist dans le champ mesures_adaptation du projet
            $mesuresAdaptationData = [
                'est_brouillon' => $estBrouillon,
                'valide' => !$estBrouillon && $criteresCompletes === $totalCriteres,
                'criteres' => $criteresFormates,
                'criteres_completes' => $criteresCompletes,
                'total_criteres' => $totalCriteres,
                'secteur_principal' => $secteurDetails,
                'sous_secteur_id' => $projet->secteurId,
                'derniere_mise_a_jour' => now(),
                'mis_a_jour_par' => auth()->id()
            ];

            $projet->update(['mesures_adaptation' => $mesuresAdaptationData]);

            // Log pour confirmer la sauvegarde
            \Log::info('Checklist adaptation sauvegardée', [
                'projet_id' => $projet->id,
                'criteres_traites' => count($criteresFormates),
                'criteres_completes' => $criteresCompletes,
                'total_criteres' => $totalCriteres,
                'est_brouillon' => $estBrouillon
            ]);

            return [
                'success' => true,
                'message' => $estBrouillon ?
                    'Checklist sauvegardée en brouillon.' :
                    'Checklist de contrôle des adaptations validée avec succès.',
                'criteres_traites' => count($criteresFormates),
                'criteres_completes' => $criteresCompletes,
                'total_criteres' => $totalCriteres,
                'est_complete' => $criteresCompletes === $totalCriteres
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement de la checklist: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traiter la checklist de suivi du rapport de préfaisabilité
     */
    private function traiterChecklistSuiviRapportPrefaisabilite($rapport, array $checklistData, bool $estBrouillon = false, array $fichiers = []): array
    {
        try {
            DB::beginTransaction();
            /*
            // Récupérer le dernier rapport de préfaisabilité s'il existe
            $rapportExistant = $projet->rapportPrefaisabilite()->first();

            // Déterminer le parent_id pour la hiérarchie (uniquement si soumission finale et rapport existe)
            $parentId = null;
            if ($rapportExistant && !$estBrouillon) {
                $parentId = $rapportExistant->id;
            }

            // Créer le nouveau rapport
            $rapport = \App\Models\Rapport::create([
                'projet_id' => $rapport->projet->id,
                'parent_id' => $parentId,
                'type' => 'prefaisabilite',
                'statut' => $estBrouillon ? 'brouillon' : 'soumis',
                'intitule' => 'Rapport de préfaisabilité - ' . $rapport->projet->titre_projet,
                'checklist_suivi' => $checklistData, // Stocker directement les données
                'info_cabinet_etude' => $fichiers['cabinet_etude'] ?? null,
                'recommandation' => $fichiers['recommandation'] ?? null,
                'date_soumission' => $estBrouillon ? null : now(),
                'soumis_par_id' => $estBrouillon ? null : auth()->id()
            ]);
            */

            // Associer les fichiers au rapport si ils existent
            if (!empty($fichiers)) {
                // Fichier rapport principal
                if (isset($fichiers['rapport'])) {
                    $this->attacherFichierAuRapport($rapport, $fichiers['rapport'], 'rapport');
                }

                // Procès verbal
                if (isset($fichiers['proces_verbal'])) {
                    $this->attacherFichierAuRapport($rapport, $fichiers['proces_verbal'], 'proces-verbal');
                }
            }

            // Traiter les données de checklist via la relation champs() si nécessaire
            $this->traiterChampsChecklistSuivi($rapport, $checklistData);

            DB::commit();

            return [
                'success' => true,
                'message' => $estBrouillon ?
                    'Checklist de suivi sauvegardée en brouillon.' :
                    'Checklist de suivi du rapport de préfaisabilité validée avec succès.',
                'rapport_id' => $rapport->id,
                'projet_id' => $rapport->projet->id,
                'est_brouillon' => $estBrouillon
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement de la checklist de suivi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir l'ID du rapport de préfaisabilité le plus récent pour un projet
     */
    private function getRapportIdForProject($projet)
    {
        $rapport = \App\Models\Rapport::where('projet_id', $projet->id)
            ->where('type', 'prefaisabilite')
            ->latest('created_at')
            ->first();

        return $rapport ? $rapport->id : null;
    }

    /**
     * Attacher un fichier à un rapport en utilisant les méthodes existantes
     */
    private function attacherFichierAuRapport($rapport, $fichier, $categorie)
    {
        if ($fichier instanceof \Illuminate\Http\UploadedFile) {
            // Utiliser les méthodes existantes selon le type de fichier
            $fichierCree = null;

            if ($categorie === 'rapport') {
                $fichierCree = $this->gererFichierRapport($rapport, $fichier, []);
            } elseif ($categorie === 'proces-verbal') {
                $fichierCree = $this->gererFichierProcesVerbal($rapport, $fichier, []);
            }

            // Associer le fichier créé au rapport si il a été créé avec succès
            if ($fichierCree) {
                // Mettre à jour le fichier pour l'associer également au rapport
                $fichierCree->update([
                    'fichier_attachable_type' => \App\Models\Rapport::class,
                    'fichier_attachable_id' => $rapport->id
                ]);
            }

            return $fichierCree;
        }

        return null;
    }

    /**
     * Traiter les champs de checklist via la relation champs()
     */
    private function traiterChampsChecklistSuivi($rapport, array $checklistData)
    {
        foreach ($checklistData as $evaluation) {
            $checkpointId   = $evaluation['checkpoint_id'];
            $remarque       = $evaluation['remarque'] ?? null;
            $explication    = $evaluation['explication'] ?? null;

            // Préparer la valeur à stocker (remarque + explication)
            $valeur = $remarque;/* [
                'remarque' => $remarque,
                'explication' => $explication,
                'checkpoint_id' => $checkpointId,
                'date_evaluation' => now()
            ]; */

            // Créer ou mettre à jour la relation champ-rapport
            $rapport->champs()->syncWithoutDetaching([
                $checkpointId => [
                    'valeur' => $valeur,
                    'commentaire' => $explication,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }

        // Préparer l'évaluation complète pour enregistrement
        $checklist_suivi =  collect($this->documentRepository->getCanevasChecklistSuiviRapportPrefaisabilite()->all_champs)->map(function ($champ) use ($rapport) {
            $champEvalue = collect($rapport->champs)->firstWhere('attribut', $champ['attribut']);
            return [
                'champ_id'          => $champ['id'],
                'label'             => $champ['label'],
                'attribut'          => $champ['attribut'],
                'ordre_affichage'   => $champ['ordre_affichage'],
                'type_champ'        => $champ['type_champ'],
                'valeur'            => $champEvalue && isset($champEvalue['pivot']['valeur']) ? $champEvalue['pivot']['valeur'] : null,
                'commentaire'       => $champEvalue && isset($champEvalue['pivot']['commentaire']) ? $champEvalue['pivot']['commentaire'] : null,
                'updated_at'        => $champEvalue && isset($champEvalue['pivot']['updated_at']) ? Carbon::parse($champEvalue['pivot']['updated_at'])->format("Y-m-d H:i:s") : null
            ];
        })->toArray();

        $rapport->checklist_suivi = $checklist_suivi;
        /*$rapport->champs->map(function ($champ) {
            return [
                'id' => $champ->id,
                'label' => $champ->label,
                'attribut' => $champ->attribut,
                'ordre_affichage' => $champ['ordre_affichage'],
                'type_champ' => $champ['type_champ'],
                'valeur' => $champ->pivot->valeur,
                'commentaire' => $champ->pivot->commentaire,
                'updated_at' => Carbon::parse($champ->pivot->updated_at)->format("Y-m-d H:i:s")
            ];
        });*/

        $rapport->save();
    }


    /**
     * Déterminer si une nouvelle version doit être créée pour le rapport
     */
    private function doitCreerNouvelleVersionRapport(Projet $projet): bool
    {
        // Pour les rapports : compter les soumissions précédentes
        $nombreSoumissions = $projet->workflows()
            ->where('statut', StatutIdee::VALIDATION_PF)
            ->count();

        return $nombreSoumissions > 0;
    }

    /**
     * Créer une nouvelle version du rapport
     */
    private function creerNouvelleVersionRapport(Projet $projet, $fichier, array $data): Fichier
    {
        // Récupérer la dernière version
        $derniereVersion = $projet->rapports_prefaisabilite()
            ->orderBy('created_at', 'desc')
            ->first();

        $nouvelleVersion = 1;
        if ($derniereVersion) {
            $versionActuelle = $derniereVersion->metadata['version'] ?? 1;
            $nouvelleVersion = $versionActuelle + 1;

            // Archiver l'ancienne version
            $derniereVersion->update([
                'is_active' => false,
                'metadata' => array_merge($derniereVersion->metadata ?? [], [
                    'statut' => 'archive',
                    'archive_le' => now(),
                    'remplace_par_version' => $nouvelleVersion
                ])
            ]);
        }

        return $this->sauvegarderFichierRapport($projet, $fichier, $data, $nouvelleVersion);
    }

    /**
     * Remplacer le rapport existant (même cycle)
     */
    private function remplacerRapportExistant(Projet $projet, $fichier, array $data): Fichier
    {
        $rapportExistant = $projet->rapports_prefaisabilite()->where('is_active', true)->first();
        $version = 1;

        if ($rapportExistant) {
            $version = $rapportExistant->metadata['version'] ?? 1;
            // Supprimer l'ancien fichier physique
            Storage::disk('public')->delete($rapportExistant->chemin);
            $rapportExistant->delete();
        }

        return $this->sauvegarderFichierRapport($projet, $fichier, $data, $version);
    }

    /**
     * Créer une nouvelle version du procès verbal
     */
    private function creerNouvelleVersionProcesVerbal(Projet $projet, $fichier, array $data): Fichier
    {
        // Récupérer la dernière version
        $derniereVersion = $projet->fichiers()
            ->where('categorie', 'proces-verbal-prefaisabilite')
            ->orderBy('created_at', 'desc')
            ->first();

        $nouvelleVersion = 1;
        if ($derniereVersion) {
            $versionActuelle = $derniereVersion->metadata['version'] ?? 1;
            $nouvelleVersion = $versionActuelle + 1;

            // Archiver l'ancienne version
            $derniereVersion->update([
                'is_active' => false,
                'metadata' => array_merge($derniereVersion->metadata ?? [], [
                    'statut' => 'archive',
                    'archive_le' => now(),
                    'remplace_par_version' => $nouvelleVersion
                ])
            ]);
        }

        return $this->sauvegarderFichierProcesVerbal($projet, $fichier, $data, $nouvelleVersion);
    }

    /**
     * Remplacer le procès verbal existant (même cycle)
     */
    private function remplacerProcesVerbalExistant(Projet $projet, $fichier, array $data): Fichier
    {
        $procesVerbalExistant = $projet->fichiers()
            ->where('categorie', 'proces-verbal-prefaisabilite')
            ->where('is_active', true)
            ->first();

        $version = 1;

        if ($procesVerbalExistant) {
            $version = $procesVerbalExistant->metadata['version'] ?? 1;
            // Supprimer l'ancien fichier physique
            Storage::disk('public')->delete($procesVerbalExistant->chemin);
            $procesVerbalExistant->delete();
        }

        return $this->sauvegarderFichierProcesVerbal($projet, $fichier, $data, $version);
    }

    /**
     * Envoyer une notification pour la validation de préfaisabilité
     */
    private function envoyerNotificationValidationPrefaisabilite($projet, string $action, array $data)
    { /* À implémenter */
    }

    /**
     * Soumettre le rapport d'évaluation ex-ante (SFD-018)
     */
    public function soumettreRapportEvaluationExAnte($projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!auth()->user()->hasPermissionTo('soumettre-un-rapport-d-evaluation-ex-ante') && auth()->user()->type !== 'dgpd' && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n\'avez pas les droits pour effectuer cette soumission.", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::MATURITE->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de maturité pour la soumission du rapport d\'évaluation ex-ante.'
                ], 422);
            }

            // Traitement et sauvegarde du rapport principal
            $rapport = null;
            if (isset($data['rapport_evaluation_ex_ante'])) {
                $rapport = $this->gererRapportEvaluationExAnte($projet, $data['rapport_evaluation_ex_ante'], $data);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Le fichier du rapport d\'évaluation ex-ante est requis pour la soumission.'
                ], 422);
            }

            // Traitement et sauvegarde des annexes
            $fichiersAnnexes = [];
            if ($rapport && isset($data['documents_annexe']) && is_array($data['documents_annexe'])) {
                foreach ($data['documents_annexe'] as $index => $annexe) {
                    $fichiersAnnexes[] = $this->gererAnnexeRapportExAnte($rapport, $annexe, $index, $data);
                }
            }

            // Changer le statut du projet
            $projet->update([
                'statut' => StatutIdee::RAPPORT,
                'phase' => $this->getPhaseFromStatut(StatutIdee::RAPPORT),
                'sous_phase' => $this->getSousPhaseFromStatut(StatutIdee::RAPPORT)
            ]);

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, StatutIdee::RAPPORT);
            $this->enregistrerDecision(
                $projet,
                "Soumission du rapport d'évaluation ex-ante",
                $data['commentaire'] ?? 'Rapport d\'évaluation ex-ante soumis pour validation',
                auth()->user()->personne->id
            );

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationSoumissionRapportExAnte($projet, $rapport, $fichiersAnnexes);

            return response()->json([
                'success' => true,
                'message' => 'Rapport d\'évaluation ex-ante soumis avec succès.',
                'data' => [
                    'projet_id' => $projet->id,
                    'ancien_statut' => StatutIdee::MATURITE->value,
                    'nouveau_statut' => StatutIdee::RAPPORT->value,
                    'rapport_principal' => $rapport ? $rapport : null,
                    'annexes' => collect($fichiersAnnexes)->map(function ($fichier) {
                        return [
                            'id' => $fichier->id,
                            'nom' => $fichier->nom_original,
                            'url' => $fichier->url
                        ];
                    }),
                    'nombre_annexes' => count($fichiersAnnexes),
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
     * Récupérer le rapport final d'analyse du projet
     */
    public function getDetailsSoumissionRapportFinale(int $projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier les permissions d'accès
            /* if (auth()->user()->profilable?->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== \App\Models\Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'accès pour effectuer cette action", 403);
            } */

            // Récupérer le rapport soumis le plus récent
            $rapport = \App\Models\Rapport::where('projet_id', $projetId)
                ->where('type', 'evaluation_ex_ante')
                ->where('statut', 'soumis')
                ->with(['fichiersRapport', 'soumisPar', 'projet', 'champs', 'documentsAnnexes'])
                ->latest('created_at')
                ->first();

            if (!$rapport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun rapport soumis trouvé pour ce projet.',
                    'data' => null
                ], 206);
            }

            return response()->json([
                'success' => true,
                'data' => new \App\Http\Resources\RapportResource($rapport),
                'message' => 'Détails de soumission du rapport de préfaisabilité récupérés avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rapport soumis: ' . $e->getMessage(),
                'data' => null
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }

    /**
     * Valider le rapport final (SFD-019)
     */
    public function validerRapportFinal($projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier les autorisations (DGPD uniquement)
            if (!auth()->user()->hasPermissionTo('valider-un-rapport-evaluation-ex-ante') && auth()->user()->type !== 'dgpd' && auth()->user()->profilable_type !== Dgpd::class) {
                throw new Exception("Vous n\'avez pas les droits pour effectuer cette soumission.", 403);
            }

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est au bon statut
            if ($projet->statut->value !== StatutIdee::RAPPORT->value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à l\'étape de validation du rapport.'
                ], 422);
            }

            // Valider l'action demandée
            $actionsPermises = ['valider', 'corriger'];
            if (!isset($data['action']) || !in_array($data['action'], $actionsPermises)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action invalide. Actions possibles: valider, corriger.'
                ], 422);
            }

            $nouveauStatut = null;
            $messageAction = '';

            // Créer une évaluation pour tracer la validation
            $evaluationValidation = $projet->evaluations()->create([
                'type_evaluation' => 'validation-final-evaluation-ex-ante',
                'projetable_type' => get_class($projet),
                'projetable_id' => $projet->id,
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => now(),
                'valider_le' => now(),
                'evaluateur_id' => auth()->id(),
                'valider_par' => auth()->id(),
                'commentaire' => $data['commentaire'] ?? $messageAction,
                'evaluation' => $data,
                'resultats_evaluation' => $data['action'],
                'statut' => 1
            ]);

            switch ($data['action']) {
                case 'valider':
                    // Projet validé → Prêt pour sélection
                    $nouveauStatut = StatutIdee::PRET;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut),
                        'date_fin_etude' => now()
                    ]);
                    $messageAction = 'Projet validé et prêt pour la sélection.';
                    break;

                case 'corriger':
                    // Corrections demandées → Retour à maturité
                    $nouveauStatut = StatutIdee::MATURITE;
                    $projet->update([
                        'statut' => $nouveauStatut,
                        'phase' => $this->getPhaseFromStatut($nouveauStatut),
                        'sous_phase' => $this->getSousPhaseFromStatut($nouveauStatut)
                    ]);
                    $messageAction = 'Corrections demandées sur le rapport d\'évaluation ex-ante.';
                    break;
            }

            // Enregistrer le workflow et la décision
            $this->enregistrerWorkflow($projet, $nouveauStatut);
            $this->enregistrerDecision(
                $projet,
                "Validation finale du rapport - " . ucfirst($data['action']),
                $data['commentaire'] ?? $messageAction,
                auth()->user()->personne->id
            );

            DB::commit();

            // Envoyer une notification
            $this->envoyerNotificationValidationFinale($projet, $data['action'], $data);

            return response()->json([
                'success' => true,
                'message' => $messageAction,
                'data' => [
                    'projet_id' => $projet->id,
                    'action' => $data['action'],
                    'ancien_statut' => StatutIdee::RAPPORT->value,
                    'nouveau_statut' => $nouveauStatut->value,
                    'commentaire' => $data['commentaire'] ?? null,
                    'valide_par' => auth()->id(),
                    'valide_le' => now()->format('d/m/Y H:i:s'),
                    'date_fin_etude' => $data['action'] === 'valider' ? now()->format('d/m/Y H:i:s') : null,
                    'pret_pour_selection' => $data['action'] === 'valider'
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidationFinal(int $projetId): JsonResponse
    {
        try {

            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier que le projet est à l'étape d'évaluation ou post-évaluation
            if (!in_array($projet->statut->value, [
                StatutIdee::EVALUATION_TDR_PF->value,
                StatutIdee::SOUMISSION_RAPPORT_PF->value,
                StatutIdee::VALIDATION_PF->value,
                StatutIdee::R_TDR_PREFAISABILITE->value,
                StatutIdee::TDR_PREFAISABILITE->value,

                StatutIdee::EVALUATION_TDR_F->value,
                StatutIdee::SOUMISSION_RAPPORT_F->value,
                StatutIdee::VALIDATION_F->value,
                StatutIdee::R_TDR_FAISABILITE->value,
                StatutIdee::TDR_FAISABILITE->value,
                StatutIdee::PRET->value,
                StatutIdee::MATURITE->value,
                StatutIdee::RAPPORT->value,
                StatutIdee::ABANDON->value
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet n\'est pas à une étape permettant la consultation des détails de validation.'
                ], 422);
            }

            // Pour le statut VALIDATION_PF, récupérer l'évaluation de validation
            $evaluationValidation = $projet->evaluations()
                ->where('type_evaluation', 'validation-final-evaluation-ex-ante')
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Détails de validation récupérés avec succès.',
                'data' => [
                    'projet' => new ProjetsResource($projet),
                    'evaluation_validation' => $evaluationValidation ? [
                        'id' => $evaluationValidation->id,
                        'evaluation' => $evaluationValidation->evaluation,
                        'decision' => $evaluationValidation->resultats_evaluation,
                        'statut' => $evaluationValidation->statut, // 0=en cours, 1=terminée
                        'evaluateur' => new UserResource($evaluationValidation->evaluateur),
                        'date_debut' => Carbon::parse($evaluationValidation->date_debut_evaluation)->format("Y-m-d h:i:s"),
                        'date_fin' => Carbon::parse($evaluationValidation->date_fin_evaluation)->format("Y-m-d h:i:s"),
                        'commentaire_global' => $evaluationValidation->commentaire
                    ] : null
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Déterminer si une nouvelle version doit être créée pour l'évaluation ex-ante
     */
    private function doitCreerNouvelleVersionEvaluationExAnte(Projet $projet): bool
    {
        // Pour l'évaluation ex-ante : compter les soumissions précédentes
        $nombreSoumissions = $projet->workflows()
            ->where('statut', StatutIdee::RAPPORT)
            ->count();

        return $nombreSoumissions > 0;
    }

    /**
     * Gérer le rapport d'évaluation ex-ante avec versioning
     */
    private function gererRapportEvaluationExAnte(Projet $projet, $fichier, array $data): ?Rapport
    {
        // Récupérer le dernier rapport d'évaluation ex-ante s'il existe
        $rapportExistant = $projet->rapportEvaluationExAnte()->first();

        // Préparer les données du rapport
        $rapportData = [
            'projet_id' => $projet->id,
            'type' => 'evaluation_ex_ante',
            'statut' => 'soumis',
            'intitule' => 'Rapport d\'évaluation ex-ante',
            'recommandation' => $data['recommandation'] ?? null,
            'info_cabinet_etude' => [
                'nom_cabinet' => $data['cabinet_etude']['nom_cabinet'] ?? null,
                'contact_cabinet' => $data['cabinet_etude']['contact_cabinet'] ?? null,
            ],
            'date_soumission' => now(),
            'soumis_par_id' => auth()->id()
        ];

        // Créer ou mettre à jour le rapport selon la même logique que préfaisabilité
        if ($rapportExistant && $rapportExistant->statut === 'soumis') {
            // Si un rapport soumis existe déjà, créer une nouvelle version
            $rapportData['parent_id'] = $rapportExistant->id;
            $rapport = Rapport::create($rapportData);
        } else {
            // Créer un nouveau rapport (première version)
            $rapport = Rapport::create($rapportData);
        }

        // Attacher le fichier au rapport
        $nouveauHash = md5_file($fichier->getRealPath());
        $fichierData = array_merge($data, [
            'hash_md5' => $nouveauHash,
            'categorie' => 'rapport',
            'ordre' => 1
        ]);

        $fichierRapport = $this->sauvegarderFichierEvaluationExAnte($rapport, $fichier, $fichierData);

        return $rapport;
    }

    /**
     * Sauvegarder le fichier d'évaluation ex-ante lié à un rapport
     */
    private function sauvegarderFichierEvaluationExAnte(Rapport $rapport, $fichier, array $data): Fichier
    {
        // Générer les informations du fichier
        $nomOriginal = $fichier->getClientOriginalName();
        $extension = $fichier->getClientOriginalExtension();
        $nomStockage = "evaluation_ex_ante_{$rapport->id}_{$data['ordre']}.{$extension}";

        // Structure du chemin : projets/hash(projet->identifiantBip)/evaluation_ex_ante/validation_final/
        $hashProjet = hash('sha256', $rapport->projet->identifiant_bip ?? $rapport->projet_id);
        $chemin = $fichier->storeAs("projets/{$hashProjet}/evaluation_ex_ante/validation_final", $nomStockage, 'local');

        // Créer l'enregistrement Fichier
        return Fichier::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'chemin' => $chemin,
            'extension' => $extension,
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => $data['hash_md5'],
            'description' => 'Rapport d\'évaluation ex-ante',
            'commentaire' => $data['commentaire'] ?? null,
            'metadata' => [
                'type_document' => 'evaluation-ex-ante',
                'rapport_id' => $rapport->id,
                'projet_id' => $rapport->projet_id,
                'version' => 1,
                'statut' => 'actif'
            ],
            'ordre' => $data['ordre'],
            'categorie' => $data['categorie'],
            'is_active' => true,
            'uploaded_by' => auth()->id(),
            'fichier_attachable_type' => 'App\\Models\\Rapport',
            'fichier_attachable_id' => $rapport->id
        ]);
    }

    /**
     * Gérer les annexes du rapport d'évaluation ex-ante
     */
    private function gererAnnexeRapportExAnte(Rapport $rapport, $fichier, int $index, array $data): ?Fichier
    {
        // Calculer le hash du nouveau fichier
        $nouveauHash = md5_file($fichier->getRealPath());

        // Vérifier s'il y a déjà une annexe identique dans ce rapport
        $annexeIdentique = $rapport->documentsAnnexes()
            ->where('hash_md5', $nouveauHash)
            ->where('ordre', $index + 2)
            ->first();

        if ($annexeIdentique) {
            return $annexeIdentique;
        }

        // Attacher l'annexe au rapport
        $annexeData = array_merge($data, [
            'hash_md5' => $nouveauHash,
            'categorie' => 'annexe',
            'ordre' => $index + 2
        ]);

        return $this->sauvegarderFichierEvaluationExAnte($rapport, $fichier, $annexeData);
    }


    /**
     * Envoyer une notification pour la soumission du rapport ex-ante
     */
    private function envoyerNotificationSoumissionRapportExAnte($projet, $rapport, array $fichiersAnnexes)
    { /* À implémenter */
    }

    /**
     * Envoyer une notification pour la validation finale
     */
    private function envoyerNotificationValidationFinale($projet, string $action, array $data)
    { /* À implémenter */
    }

    /**
     * Récupérer le rapport soumis pour un projet
     */
    public function getRapportSoumis(int $projetId): JsonResponse
    {
        try {
            // Récupérer le projet
            $projet = $this->projetRepository->findOrFail($projetId);

            // Vérifier les permissions d'accès
            if (auth()->user()->profilable->ministere?->id !== $projet->ministere->id && auth()->user()->profilable_type !== \App\Models\Dgpd::class) {
                throw new Exception("Vous n'avez pas les droits d'accès pour effectuer cette action", 403);
            }

            // Récupérer le rapport soumis le plus récent
            $rapport = \App\Models\Rapport::where('projet_id', $projetId)
                ->where('type', 'prefaisabilite')
                ->where('statut', 'soumis')
                ->with(['fichiers', 'soumisPar', 'projet'])
                ->latest('created_at')
                ->first();

            if (!$rapport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun rapport soumis trouvé pour ce projet.',
                    'data' => null
                ], 404);
            }

            // Structurer les données de retour
            $rapportData = [
                'id' => $rapport->id,
                'intitule' => $rapport->intitule,
                'type' => $rapport->type,
                'statut' => $rapport->statut,
                'checklist_suivi' => $rapport->checklist_suivi,
                'info_cabinet_etude' => $rapport->info_cabinet_etude,
                'soumis_par' => $rapport->soumisPar ? [
                    'id' => $rapport->soumisPar->id,
                    'nom' => $rapport->soumisPar->nom,
                    'prenoms' => $rapport->soumisPar->prenoms,
                    'email' => $rapport->soumisPar->email
                ] : null,
                'soumis_le' => $rapport->soumis_le,
                'created_at' => $rapport->created_at,
                'updated_at' => $rapport->updated_at,
                'fichiers' => $rapport->fichiers->map(function ($fichier) {
                    return [
                        'id' => $fichier->id,
                        'nom_original' => $fichier->nom_original,
                        'extension' => $fichier->extension,
                        'taille' => $fichier->taille,
                        'mime_type' => $fichier->mime_type,
                        'description' => $fichier->description,
                        'categorie' => $fichier->categorie,
                        'hash_acces' => $fichier->hash_md5,
                        'lien_view' => route('api.fichiers.view', ['hash' => $fichier->hash_md5]),
                        'lien_download' => route('api.fichiers.download', ['hash' => $fichier->hash_md5]),
                        'created_at' => $fichier->created_at
                    ];
                }),
                'projet' => [
                    'id' => $rapport->projet->id,
                    'titre_projet' => $rapport->projet->titre_projet,
                    'statut' => $rapport->projet->statut,
                    'ministere' => [
                        'id' => $rapport->projet->ministere->id,
                        'nom' => $rapport->projet->ministere->nom
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $rapportData,
                'message' => 'Rapport soumis récupéré avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rapport soumis: ' . $e->getMessage(),
                'data' => null
            ], $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500);
        }
    }

    /**
     * Vérifier la cohérence du suivi rapport entre soumission et validation
     */
    private function verifierCoherenceSuiviRapport($projet, array $checklistSuiviValidation): array
    {
        try {
            // Récupérer le dernier rapport de préfaisabilité soumis
            $rapportPrefaisabilite = $projet->rapportPrefaisabilite()
                ->where('statut', 'soumis')
                ->latest('created_at')
                ->first();

            if (!$rapportPrefaisabilite) {
                return [
                    'success' => false,
                    'message' => 'Aucun rapport de préfaisabilité soumis trouvé pour effectuer la vérification de cohérence.'
                ];
            }

            // Récupérer la checklist de suivi du rapport de préfaisabilité
            $checklistSuiviSoumission = $rapportPrefaisabilite->checklist_suivi;

            if (!$checklistSuiviSoumission || !is_array($checklistSuiviSoumission)) {
                return [
                    'success' => false,
                    'message' => 'Aucune checklist de suivi trouvée dans le rapport de préfaisabilité soumis.'
                ];
            }

            // Comparer les checkpoints entre soumission et validation
            $incoherences = [];
            $checkpointsSoumission = collect($checklistSuiviSoumission);
            $checkpointsValidation = collect($checklistSuiviValidation);

            // Log pour debug
            \Log::info('Vérification cohérence suivi rapport', [
                'projet_id' => $projet->id,
                'checkpoints_soumission' => $checkpointsSoumission->count(),
                'checkpoints_validation' => $checkpointsValidation->count()
            ]);

            // Vérifier que tous les checkpoints de la soumission sont présents dans la validation
            foreach ($checkpointsSoumission as $index => $checkpointSoumis) {
                $checkpointId = $checkpointSoumis['id'] ?? null;

                if (!$checkpointId) {
                    continue;
                }

                $checkpointValidation = $checkpointsValidation->firstWhere('checkpoint_id', $checkpointId);

                if (!$checkpointValidation) {
                    $incoherences[] = [
                        'type' => 'checkpoint_manquant',
                        'checkpoint_id' => $checkpointId,
                        'message' => "Le checkpoint {$checkpointId} présent lors de la soumission n'est pas trouvé dans la validation."
                    ];
                }

                // Comparer les données entre soumission et validation
                /*if ($checkpointValidation) {
                    // Comparer les remarques
                    $remarqueSoumission = $checkpointSoumis['remarque'] ?? null;
                    $remarqueValidation = $checkpointValidation['remarque'] ?? null;

                    // Vérifier si une remarque était présente à la soumission mais absente à la validation
                    if (!empty($remarqueSoumission) && empty($remarqueValidation)) {
                        $incoherences[] = [
                            'type' => 'remarque_manquante',
                            'checkpoint_id' => $checkpointId,
                            'soumission' => $remarqueSoumission,
                            'validation' => 'vide',
                            'message' => "Le checkpoint {$checkpointId} avait une remarque lors de la soumission mais n'en a pas lors de la validation."
                        ];
                    }

                    // Vérifier si les remarques sont différentes (changement significatif)
                    if (!empty($remarqueSoumission) && !empty($remarqueValidation) && $remarqueSoumission !== $remarqueValidation) {
                        $incoherences[] = [
                            'type' => 'remarque_modifiee',
                            'checkpoint_id' => $checkpointId,
                            'soumission' => $remarqueSoumission,
                            'validation' => $remarqueValidation,
                            'message' => "Le checkpoint {$checkpointId} a une remarque différente entre la soumission et la validation."
                        ];
                    }

                    // Comparer les explications
                    $explicationSoumission = $checkpointSoumis['explication'] ?? null;
                    $explicationValidation = $checkpointValidation['explication'] ?? null;

                    // Vérifier si une explication était présente à la soumission mais absente à la validation
                    if (!empty($explicationSoumission) && empty($explicationValidation)) {
                        $incoherences[] = [
                            'type' => 'explication_manquante',
                            'checkpoint_id' => $checkpointId,
                            'soumission' => $explicationSoumission,
                            'validation' => 'vide',
                            'message' => "Le checkpoint {$checkpointId} avait une explication lors de la soumission mais n'en a pas lors de la validation."
                        ];
                    }

                    // Vérifier si les explications sont différentes
                    if (!empty($explicationSoumission) && !empty($explicationValidation) && $explicationSoumission !== $explicationValidation) {
                        $incoherences[] = [
                            'type' => 'explication_modifiee',
                            'checkpoint_id' => $checkpointId,
                            'soumission' => $explicationSoumission,
                            'validation' => $explicationValidation,
                            'message' => "Le checkpoint {$checkpointId} a une explication différente entre la soumission et la validation."
                        ];
                    }
                }*/
            }

            // Vérifier s'il y a des checkpoints supplémentaires dans la validation
            foreach ($checkpointsValidation as $checkpointValidation) {
                $checkpointId = $checkpointValidation['checkpoint_id'] ?? null;

                if (!$checkpointId) {
                    continue;
                }

                $checkpointSoumis = $checkpointsSoumission->firstWhere('id', $checkpointId);

                if (!$checkpointSoumis) {
                    $incoherences[] = [
                        'type' => 'checkpoint_supplementaire',
                        'checkpoint_id' => $checkpointId,
                        'message' => "Le checkpoint {$checkpointId} est présent dans la validation mais n'était pas dans la soumission."
                    ];
                }
            }

            // S'il y a des incohérences, retourner une erreur
            if (!empty($incoherences)) {
                \Log::warning('Incohérences détectées lors de la validation', [
                    'projet_id' => $projet->id,
                    'nb_incoherences' => count($incoherences),
                    'incoherences' => $incoherences
                ]);

                return [
                    'success' => false,
                    'message' => 'Incohérences détectées entre le rapport soumis et les données de validation.',
                    'incoherences' => $incoherences
                ];
            }

            \Log::info('Vérification cohérence réussie', [
                'projet_id' => $projet->id,
                'checkpoints_verifies' => $checkpointsSoumission->count()
            ]);

            return [
                'success' => true,
                'message' => 'Vérification de cohérence réussie.',
                'checkpoints_verifies' => $checkpointsSoumission->count()
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification de cohérence', [
                'projet_id' => $projet->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification de cohérence: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Attacher le fichier rapport de validation de l'étude de préfaisabilité
     */
    private function attacherFichierRapportValidation($projet, $fichier, $evaluation)
    {
        if (!$fichier instanceof \Illuminate\Http\UploadedFile) {
            return null;
        }

        // Hasher l'identifiant BIP selon le pattern projets/{hash_identifiant_bip}/Evaluation-ex-ante/etude_prefaisabilite/rapport_validation
        $hashedIdentifiantBip = hash('sha256', $projet->identifiant_bip);

        // Générer un nom de fichier unique
        $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $fichier->hashName();

        // Stocker le fichier selon le pattern de hash avec structure Evaluation-ex-ante
        $path = $fichier->storeAs(
            "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_prefaisabilite/rapport-validation",
            $nomStockage,
            'local'
        );

        // Créer l'enregistrement du fichier via la relation polymorphe
        $fichierCree = $projet->fichiers()->create([
            'nom_original' => $fichier->getClientOriginalName(),
            'nom_stockage' => $nomStockage,
            'chemin' => $path,
            'extension' => $fichier->getClientOriginalExtension(),
            'mime_type' => $fichier->getMimeType(),
            'taille' => $fichier->getSize(),
            'hash_md5' => md5_file($fichier->getRealPath()),
            'description' => 'Rapport de validation de l\'étude de préfaisabilité',
            'commentaire' => 'Document de validation soumis par la DGPD',
            'categorie' => 'rapport-validation-prefaisabilite',
            'uploaded_by' => auth()->id(),
            'is_public' => false,
            'is_active' => true,
            'metadata' => [
                'evaluation_id' => $evaluation->id,
                'type_validation' => 'etude-prefaisabilite',
                'action_validation' => $evaluation->resultats_evaluation,
                'uploaded_context' => 'validation-etude-prefaisabilite',
                'soumis_par' => auth()->id(),
                'soumis_le' => now()->toISOString(),
                'folder_structure' => "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_prefaisabilite/rapport_validation"
            ]
        ]);

        return $fichierCree;
    }

    /**
     * Créer ou récupérer la structure de dossiers pour un projet
     */
    private function getOrCreateProjetFolderStructure($projet, $sousType = null)
    {
        try {
            // Hasher l'identifiant BIP selon le nouveau pattern projets/{hash_identifiant_bip}/Evaluation-ex-ante/etude_prefaisabilite/{sousType}
            $hashedIdentifiantBip = hash('sha256', $projet->identifiant_bip);
            $cheminBase = "Projets/{$hashedIdentifiantBip}/Evaluation-ex-ante/etude_prefaisabilite";
            $cheminComplet = $sousType ? $cheminBase . '/' . $sousType : $cheminBase;

            // Chercher si le dossier existe déjà
            $dossierExistant = Dossier::where('full_path', $cheminComplet)->first();

            if ($dossierExistant) {
                return $dossierExistant;
            }

            // Créer la structure de dossiers si elle n'existe pas
            $dossierParent = null;

            // Créer d'abord le dossier racine du projet avec la nouvelle structure
            $dossierProjet = Dossier::firstOrCreate(
                ['full_path' => $cheminBase],
                [
                    'nom' => 'etude_prefaisabilite',
                    'description' => 'Dossier étude de préfaisabilité du projet ' . $projet->titre_projet . ' (' . $projet->identifiant_bip . ')',
                    'parent_id' => null,
                    'is_public' => false,
                    'created_by' => auth()->id(),
                    'metadata' => [
                        'type' => 'dossier-etude-prefaisabilite',
                        'projet_id' => $projet->id,
                        'identifiant_bip' => $projet->identifiant_bip,
                        'hashed_identifiant_bip' => $hashedIdentifiantBip,
                        'structure' => 'Evaluation-ex-ante/etude_prefaisabilite'
                    ]
                ]
            );

            // Si un sous-type est spécifié, créer le sous-dossier
            if ($sousType) {
                $dossierSousType = Dossier::firstOrCreate(
                    ['full_path' => $cheminComplet],
                    [
                        'nom' => $sousType,
                        'description' => 'Dossier ' . $sousType . ' du projet ' . $projet->identifiant_bip,
                        'parent_id' => $dossierProjet->id,
                        'is_public' => false,
                        'created_by' => auth()->id(),
                        'metadata' => [
                            'type' => 'sous-dossier-etude-prefaisabilite',
                            'sous_type' => $sousType,
                            'projet_id' => $projet->id,
                            'identifiant_bip' => $projet->identifiant_bip,
                            'hashed_identifiant_bip' => $hashedIdentifiantBip,
                            'structure' => "Evaluation-ex-ante/etude_prefaisabilite/{$sousType}"
                        ]
                    ]
                );

                return $dossierSousType;
            }

            return $dossierProjet;
        } catch (\Exception $e) {
            \Log::warning('Erreur lors de la création de la structure de dossiers étude préfaisabilité', [
                'error' => $e->getMessage(),
                'projet_id' => $projet->id,
                'identifiant_bip' => $projet->identifiant_bip,
                'hashed_identifiant_bip' => hash('sha256', $projet->identifiant_bip),
                'sous_type' => $sousType
            ]);
            return null;
        }
    }

    /**
     * Vérifier que tous les checkpoints sont complétés avant validation
     */
    private function verifierCompletude(array $checklistSuiviValidation): array
    {
        try {
            $checkpointsIncomplets = [];

            foreach ($checklistSuiviValidation as $checkpoint) {
                $checkpointId = $checkpoint['checkpoint_id'] ?? null;
                $remarque = $checkpoint['remarque'] ?? null;
                $explication = $checkpoint['explication'] ?? null;

                if (!$checkpointId) {
                    continue;
                }

                // Vérifier que le checkpoint a au moins une remarque OU une explication
                if (empty($remarque) && empty($explication)) {
                    $checkpointsIncomplets[] = [
                        'checkpoint_id' => $checkpointId,
                        'message' => "Le checkpoint {$checkpointId} doit avoir au moins une remarque ou une explication."
                    ];
                }
            }

            // S'il y a des checkpoints incomplets, empêcher la validation
            if (!empty($checkpointsIncomplets)) {
                \Log::warning('Tentative de validation avec checkpoints incomplets', [
                    'nb_checkpoints_incomplets' => count($checkpointsIncomplets),
                    'checkpoints_incomplets' => array_column($checkpointsIncomplets, 'checkpoint_id')
                ]);

                return [
                    'success' => false,
                    'message' => 'Impossible de valider le projet : ' . count($checkpointsIncomplets) . ' checkpoint(s) sont incomplets. Tous les checkpoints doivent avoir au moins une remarque ou une explication.',
                    'checkpoints_incomplets' => $checkpointsIncomplets
                ];
            }

            \Log::info('Tous les checkpoints sont complétés', [
                'nb_checkpoints_verifies' => count($checklistSuiviValidation)
            ]);

            return [
                'success' => true,
                'message' => 'Tous les checkpoints sont complétés.',
                'nb_checkpoints_complets' => count($checklistSuiviValidation)
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification de complétude des checkpoints', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification de complétude: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Créer ou récupérer la structure de dossiers pour les TDRs de préfaisabilité
     */
    private function getOrCreateTdrFolderStructure($projetId, string $type = 'tdr'): ?Dossier
    {
        try {
            // Récupérer le projet pour avoir l'identifiant BIP
            $projet = \App\Models\Projet::find($projetId);
            if (!$projet) {
                return null;
            }

            // 1. Dossier racine : "Projets"
            $dossierRacine = Dossier::firstOrCreate([
                'nom' => 'Projets',
                'parent_id' => null
            ], [
                'nom' => 'Projets',
                'description' => 'Dossier principal contenant tous les projets BIP',
                'parent_id' => null,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#2563EB',
                'icone' => 'collection'
            ]);

            // 2. Sous-dossier : Identifiant BIP du projet
            $dossierProjet = Dossier::firstOrCreate([
                'nom' => $projet->identifiant_bip,
                'parent_id' => $dossierRacine->id
            ], [
                'nom' => $projet->identifiant_bip,
                'description' => 'Documents du projet ' . $projet->identifiant_bip,
                'parent_id' => $dossierRacine->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#059669',
                'icone' => 'folder'
            ]);

            // 3. Sous-dossier : "Evaluation ex-ante"
            $dossierEvaluation = Dossier::firstOrCreate([
                'nom' => 'Evaluation ex-ante',
                'parent_id' => $dossierProjet->id
            ], [
                'nom' => 'Evaluation ex-ante',
                'description' => 'Documents d\'évaluation ex-ante du projet',
                'parent_id' => $dossierProjet->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#7C3AED',
                'icone' => 'chart-pie'
            ]);

            // 4. Sous-dossier : "Etude de préfaisabilité"
            $dossierEtude = Dossier::firstOrCreate([
                'nom' => 'Etude de préfaisabilité',
                'parent_id' => $dossierEvaluation->id
            ], [
                'nom' => 'Etude de préfaisabilité',
                'description' => 'Documents de l\'étude de préfaisabilité',
                'parent_id' => $dossierEvaluation->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#DC2626',
                'icone' => 'document-text'
            ]);

            // 5. Sous-dossier : "Termes de référence"
            $dossierTdr = Dossier::firstOrCreate([
                'nom' => 'Termes de référence',
                'parent_id' => $dossierEtude->id
            ], [
                'nom' => 'Termes de référence',
                'description' => 'Termes de référence pour l\'étude de préfaisabilité',
                'parent_id' => $dossierEtude->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#F59E0B',
                'icone' => 'clipboard-list'
            ]);

            // 6. Sous-dossier selon le type
            $nomSousDossier = match ($type) {
                'autres-documents' => 'Autres documents',
                'tdr' => 'Documents TDR',
                'rapports' => 'Rapports',
                default => 'Documents TDR'
            };

            $descriptionSousDossier = match ($type) {
                'autres-documents' => 'Autres documents annexes aux TDR',
                'tdr' => 'Documents des termes de référence',
                'rapports' => 'Rapports d\'étude de préfaisabilité',
                default => 'Documents des termes de référence'
            };

            $couleurSousDossier = match ($type) {
                'autres-documents' => '#6B7280',
                'tdr' => '#10B981',
                'rapports' => '#EF4444',
                default => '#10B981'
            };

            $iconeSousDossier = match ($type) {
                'autres-documents' => 'document-duplicate',
                'tdr' => 'document-text',
                'rapports' => 'document-report',
                default => 'document-text'
            };

            $sousDossierFinal = Dossier::firstOrCreate([
                'nom' => $nomSousDossier,
                'parent_id' => $dossierTdr->id
            ], [
                'nom' => $nomSousDossier,
                'description' => $descriptionSousDossier,
                'parent_id' => $dossierTdr->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => $couleurSousDossier,
                'icone' => $iconeSousDossier
            ]);

            return $sousDossierFinal;
        } catch (\Exception $e) {
            // En cas d'erreur, retourner null et laisser le fichier sans dossier
            \Log::warning('Erreur lors de la création de la structure de dossiers TDR', [
                'error' => $e->getMessage(),
                'projet_id' => $projetId,
                'type' => $type
            ]);
            return null;
        }
    }
}
