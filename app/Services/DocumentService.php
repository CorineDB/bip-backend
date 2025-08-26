<?php

namespace App\Services;

use App\Http\Resources\CanevasAppreciationTdrResource;
use App\Http\Resources\CanevasRedactionTdrPrefaisabiliteResource;
use App\Http\Resources\CanevasRedactionTdrFaisabiliteResource;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\CanevasNoteConceptuelleResource;
use App\Models\CategorieDocument;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\Contracts\DocumentServiceInterface;
use App\Services\DocumentStructureService;

class DocumentService extends BaseService implements DocumentServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected DocumentStructureService $structureService;

    public function __construct(
        DocumentRepositoryInterface $repository,
        DocumentStructureService $structureService
    ) {
        parent::__construct($repository);
        $this->structureService = $structureService;
    }

    protected function getResourceClass(): string
    {
        return DocumentResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Extraire les données relationnelles avant création
            $sectionsData = $data['sections'] ?? [];
            $champsData = $data['champs'] ?? [];

            // Nettoyer les données du document principal
            $documentData = collect($data)->except(['sections', 'champs'])->toArray();

            // Créer le document principal
            $document = $this->repository->create($documentData);

            // Traiter les sections avec leurs champs
            if (!empty($sectionsData)) {
                $this->createSectionsWithChamps($document, $sectionsData);
            }

            // Traiter les champs directs (sans section)
            if (!empty($champsData)) {
                $this->createDirectChamps($document, $champsData);
            }

            // Recharger le document avec ses relations
            $document->load(['sections.champs', 'champs', 'categorie']);

            // Générer et sauvegarder la structure JSON resource
            $this->structureService->generateAndSaveStructure($document);

            DB::commit();

            return (new $this->resourceClass($document))
                ->additional(['message' => 'Document créé avec succès.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Créer les sections avec leurs champs associés
     */
    private function createSectionsWithChamps($document, array $sectionsData, $sectionParent = null): void
    {
        foreach ($sectionsData as $sectionData) {
            $section = $document->sections()->create([
                'intitule' => $sectionData['intitule'],
                'description' => $sectionData['description'],
                'ordre_affichage' => $sectionData['ordre_affichage'],
                'type' => $sectionData['type'] ?? null,
                'parentSectionId' =>  $sectionParent ? $sectionParent->id : null
            ]);

            // Créer les champs de cette section si fournis
            if (isset($sectionData['champs']) && is_array($sectionData['champs'])) {
                foreach ($sectionData['champs'] as $champData) {
                    $this->createChamp($champData, $document, $section);
                }
            }

            // Créer les sous-sections si elles existent
            if (isset($sectionData['sous_sections']) && is_array($sectionData['sous_sections'])) {
                foreach ($sectionData['sous_sections'] as $sousSectionData) {
                    $this->createSectionsWithChamps($sousSectionData, $document, $section);
                }
            }
        }
    }

    /**
     * Créer les champs directement attachés au document (sans section)
     */
    private function createDirectChamps($document, array $champsData): void
    {
        foreach ($champsData as $champData) {
            // Vérifier si secteurId est fourni et correspond à une section existante
            $section = null;
            if (isset($champData['secteurId'])) {
                $section = $document->sections()->find($champData['secteurId']);
                if (!$section) {
                    throw new Exception("Section avec ID {$champData['secteurId']} introuvable pour ce document");
                }
            }

            $this->createChamp($champData, $document, $section);
        }
    }

    /**
     * Créer ou mettre à jour un champ avec validation des données
     */
    private function createChamp(array $champData, $document, $section = null): void
    {
        $champAttributes = [
            'label' => $champData['label'],
            'info' => $champData['info'] ?? null,
            'attribut' => $champData['attribut'] ?? null,
            'placeholder' => $champData['placeholder'] ?? null,
            'is_required' => $champData['is_required'] ?? false,
            'champ_standard' => $champData['champ_standard'] ?? false,
            'isEvaluated' => $champData['isEvaluated'] ?? false,
            'default_value' => $champData['default_value'] ?? null,
            'commentaire' => $champData['commentaire'] ?? null,
            'ordre_affichage' => $champData['ordre_affichage'],
            'type_champ' => $champData['type_champ'],
            'meta_options' => $champData['meta_options'] ?? [],
            'documentId' => $document->id,
            'sectionId' => $section ? $section->id : null
        ];

        $this->createOrUpdateChamp($champAttributes, $document, $section);
    }

    /**
     * Méthode utilitaire pour créer ou mettre à jour un champ
     */
    private function createOrUpdateChamp(array $champAttributes, $document, $section = null): void
    {
        // Critères uniques pour identifier un champ existant
        $uniqueKeys = [
            'attribut' => $champAttributes['attribut'],
            'sectionId' => $section ? $section->id : null,
            'documentId' => $document->id
        ];

        // Utiliser updateOrCreate pour éviter les doublons
        if ($section) {
            $object = $section->champs()->updateOrCreate($uniqueKeys, $champAttributes);
        } else {
            $object = $document->champs()->updateOrCreate($uniqueKeys, $champAttributes);
        }
    }

    /**
     * Mettre à jour les sections avec leurs champs pour un document existant
     */
    private function updateSectionsWithChamps($document, array $sectionsData, $sectionParent = null)
    {
        foreach ($sectionsData as $sectionData) {
            $sectionId = $sectionData['id'] ?? null;
            $champsData = $sectionData['champs'] ?? [];
            $sousSectionsData = $sectionData['sous_sections'] ?? [];

            // Extraire les données de la section (sans les champs)
            $sectionAttributes = collect($sectionData)->except(['id', 'champs'])->toArray();
            $sectionAttributes["parentSectionId"] = $sectionParent ? $sectionParent->id : null;

            if ($sectionId) {
                // Mettre à jour section existante
                $section = $document->sections()->find($sectionId);
                if ($section) {
                    $section->fill($sectionAttributes);
                    $section->save();
                    //$section->update($sectionAttributes);
                } else {
                    // Section n'existe pas, la créer
                    $section = $document->sections()->create($sectionAttributes);
                }
            } else {
                // Créer nouvelle section
                $section = $document->sections()->create($sectionAttributes);
            }

            // Créer les sous-sections si elles existent

            // Traiter les champs de la section
            if (!empty($sousSectionsData)) {
                $sousSectionData = $sectionData['sous_sections'];
                $this->updateSectionsWithChamps($document, $sousSectionData, $section);
            }

            // Traiter les champs de la section
            if (!empty($champsData)) {
                $this->updateChampsForSection($section, $champsData);
            }
        }
    }

    /**
     * Mettre à jour les champs d'une section
     */
    private function updateChampsForSection($section, array $champsData)
    {
        foreach ($champsData as $champData) {
            $champId = $champData['id'] ?? null;
            $champAttribut = $champData['attribut'] ?? null;

            // Extraire les données du champ
            $champAttributes = collect($champData)->except(['id', 'sectionId'])->toArray();
            $champAttributes['sectionId'] = $section->id;

            $champ = null;

            // Essayer de trouver le champ par ID d'abord
            if ($champId) {
                $champ = $section->document->all_champs()->find($champId);
            }

            // Si pas trouvé par ID, chercher par attribut dans tout le document
            if (!$champ && $champAttribut) {
                $champ = $section->document->all_champs()->where('attribut', $champAttribut)->first();
            }

            if ($champ) {
                // Vérifier s'il y a déjà un champ avec le même attribut dans la section cible
                $existingChampInSection = $section->champs()
                    ->where('attribut', $champAttribut)
                    ->where('id', '!=', $champ->id)
                    ->first();

                if ($existingChampInSection) {
                    // Il existe déjà un champ avec le même attribut dans cette section
                    // Supprimer le champ existant dans la section cible et déplacer l'autre
                    $existingChampInSection->delete();
                }

                // Vérifier si le champ est déjà dans la section cible
                if ($champ->sectionId == $section->id) {
                    // Cas 1: Le champ est déjà dans cette section, juste le mettre à jour
                    $champ->update($champAttributes);
                } else {
                    // Cas 2: Le champ est dans une autre section, il faut le déplacer

                    // Vérifier s'il y a déjà un autre champ avec le même attribut dans la section cible
                    $existingChampInSection = $section->champs()
                        ->where('attribut', $champAttribut)
                        ->where('id', '!=', $champ->id)
                        ->first();

                    if ($existingChampInSection) {
                        // Il existe déjà un champ avec le même attribut dans cette section
                        // Supprimer le champ existant dans la section cible pour éviter le conflit
                        $existingChampInSection->delete();
                    }

                    // Déplacer le champ vers la nouvelle section
                    $champ->update($champAttributes);
                }
            } else {
                // Cas 3: Aucun champ existant trouvé, en créer un nouveau
                $this->createOrUpdateChamp($champAttributes, $section->document, $section);
            }


            /*
                if ($champId) {
                    // Mettre à jour champ existant
                    $champ = $section->champs()->find($champId);
                    if ($champ) {
                        $champ->fill($champAttributes);
                        $champ->save();
                        dump($champAttributes);
                        $this->createOrUpdateChamp($champAttributes, $section->document, $section);
                        //$champ->update($champAttributes);
                    } else {
                        // Champ n'existe pas, le créer
                        $this->createOrUpdateChamp($champAttributes, $section->document, $section);
                    }
                } else {
                    // Créer nouveau champ
                    $this->createOrUpdateChamp($champAttributes, $section->document, $section);
                }
            */
        }
    }
    /**
     * Mettre à jour les champs directs (sans section) pour un document existant
     */
    private function updateChampsDirects($document, array $champsData)
    {
        foreach ($champsData as $champData) {
            $champId = $champData['id'] ?? null;

            // Extraire les données du champ
            $champAttributes = collect($champData)->except(['id'])->toArray();

            if ($champId) {
                // Mettre à jour champ existant
                $champ = $document->champs()->find($champId);
                if ($champ) {
                    $champ->fill($champAttributes);
                    $champ->save();
                    $champ->update($champAttributes);
                } else {
                    // Champ n'existe pas, le créer
                    $this->createOrUpdateChamp($champAttributes, $document);
                }
            } else {
                // Créer nouveau champ
                $this->createOrUpdateChamp($champAttributes, $document);
            }
        }
    }

    public function createOrUpdateFicheIdee(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $ficheIdee = $this->repository->getFicheIdee();

            $data['categorieId'] = CategorieDocument::where('slug', 'fiche-idee')->firstOrFail()->id;

            if ($ficheIdee) {
                // Mode mise à jour
                /*$document = $ficheIdee;
                if (!$document) {
                    return $this->errorResponse(new Exception('Document non trouvé'), 404);
                }*/

                // Nettoyer les données du document principal
                $documentData = collect($data)->except(['sections', 'champs', 'id'])->toArray();

                // Mettre à jour le document principal
                $ficheIdee->update($documentData); //$this->repository->update($id, $documentData);

                $ficheIdee->refresh();

                // Extraire les données relationnelles
                $sectionsData = $data['sections'] ?? [];
                $champsData = $data['champs'] ?? [];

                // Traiter les sections avec leurs champs
                if (!empty($sectionsData)) {
                    $this->updateSectionsWithChamps($ficheIdee, $sectionsData);
                }

                // Traiter les champs directs (sans section)
                if (!empty($champsData)) {
                    $this->updateChampsDirects($ficheIdee, $champsData);
                }

                $ficheIdee->refresh();

                // Recharger avec toutes les relations et générer la structure
                //$ficheIdee->load(['sections.champs', 'champs', 'categorie']);
                //$this->structureService->generateAndSaveStructure($ficheIdee);

                DB::commit();

                return (new $this->resourceClass($ficheIdee))
                    ->additional(['message' => 'Fiche idée mise à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                // Mode création
                // Extraire les données relationnelles avant création
                $sectionsData = $data['sections'] ?? [];
                $champsData = $data['champs'] ?? [];

                // Nettoyer les données du document principal
                $documentData = collect($data)->except(['sections', 'champs', 'id'])->toArray();


                // Créer le document principal
                $document = $this->repository->create($documentData);

                // Traiter les sections avec leurs champs
                if (!empty($sectionsData)) {
                    $this->createSectionsWithChamps($document, $sectionsData);
                }

                // Traiter les champs directs (sans section)
                if (!empty($champsData)) {
                    $this->createDirectChamps($document, $champsData);
                }

                // Recharger avec toutes les relations et générer la structure
                $document->load(['sections.champs', 'champs', 'categorie']);
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                return (new $this->resourceClass($document->fresh(['sections.champs', 'champs'])))
                    ->additional(['message' => 'Fiche idée créée avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function ficheIdee(): JsonResponse
    {
        try {
            // Récupérer la fiche idée unique
            $ficheIdee = $this->repository->getFicheIdee();

            if (!$ficheIdee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune fiche idée trouvée.'
                ], 404);
            }

            return (new $this->resourceClass($ficheIdee))
                ->additional(['message' => 'Fiche idée récupérée avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }


    public function canevasRedactionNoteConceptuelle(): JsonResponse
    {
        try {
            // Récupérer le canevas de note conceptuelle unique
            $canevas = $this->repository->getCanevasRedactionNoteConceptuelle();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction de note conceptuelle trouvé.'
                ], 404);
            }

            return (new CanevasNoteConceptuelleResource($canevas))
                ->additional(['message' => 'Canevas de note conceptuelle récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasRedactionNoteConceptuelle(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasRedactionNoteConceptuelle();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-redaction-note-conceptuelle')->firstOrFail()->id;

            if ($canevas) {
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->update($documentData);
                $canevas->refresh();

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // Supprimer les éléments non présents dans le payload
                $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasRedactionNoteConceptuelle();

                return (new CanevasNoteConceptuelleResource($canevas))
                    ->additional(['message' => 'Canevas de note conceptuelle mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasRedactionNoteConceptuelle();

                return (new CanevasNoteConceptuelleResource($document))
                    ->additional(['message' => 'Canevas de note conceptuelle créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Traiter les données du payload forms de manière récursive
     */
    private function processFormsData($document, array $formsData, $parentSection = null): void
    {
        foreach ($formsData as $element) {
            $this->processFormElement($document, $element, $parentSection);
        }
    }

    /**
     * Traiter un élément du formulaire de manière récursive
     */
    private function processFormElement($document, array $element, $parentSection = null): void
    {
        if ($element['element_type'] === 'field') {
            $this->createFieldFromElement($document, $element, $parentSection);
        } elseif ($element['element_type'] === 'section') {
            $section = $this->createSectionFromElement($document, $element, $parentSection);

            // Traiter récursivement les éléments enfants
            if (isset($element['elements']) && is_array($element['elements'])) {
                $this->processFormsData($document, $element['elements'], $section);
            }
        }
    }

    /**
     * Créer un champ à partir d'un élément du formulaire
     */
    private function createFieldFromElement($document, array $fieldData, $section = null): void
    {
        $champAttributes = [
            'label' => $fieldData['label'],
            'info' => $fieldData['info'] ?? '',
            'attribut' => $fieldData['attribut'],
            'placeholder' => $fieldData['placeholder'] ?? '',
            'is_required' => $fieldData['is_required'] ?? false,
            'default_value' => $fieldData['default_value'] ?? null,
            'isEvaluated' => $fieldData['isEvaluated'] ?? false,
            'ordre_affichage' => $fieldData['ordre_affichage'],
            'type_champ' => $fieldData['type_champ'],
            'meta_options' => $fieldData['meta_options'] ?? [],
            'champ_standard' => $fieldData['champ_standard'] ?? false,
            'startWithNewLine' => $fieldData['startWithNewLine'] ?? null,
            'documentId' => $document->id,
            'sectionId' => $section ? $section->id : null
        ];

        $this->createOrUpdateChamp($champAttributes, $document, $section);
    }

    /**
     * Créer une section à partir d'un élément du formulaire
     */
    private function createSectionFromElement($document, array $sectionData, $parentSection = null)
    {
        $section = $document->sections()->create([
            'intitule' => $sectionData['intitule'],
            'description' => $sectionData['description'] ?? '',
            'ordre_affichage' => $sectionData['ordre_affichage'],
            'type' => $sectionData['type'] ?? 'formulaire',
            'slug' => $sectionData['key'] ?? \Illuminate\Support\Str::slug($sectionData['intitule']),
            'parentSectionId' => $parentSection ? $parentSection->id : null
        ]);

        return $section;
    }

    /**
     * Collecter tous les IDs présents dans le payload de manière récursive
     */
    private function collectAllIds(array $formsData): array
    {
        $ids = ['champs' => [], 'sections' => []];

        foreach ($formsData as $element) {
            $this->collectElementIds($element, $ids);
        }

        return $ids;
    }

    /**
     * Collecter les IDs d'un élément de manière récursive
     */
    private function collectElementIds(array $element, array &$ids): void
    {
        // Collecter l'ID de l'élément actuel s'il existe
        if (isset($element['id']) && $element['id']) {
            if ($element['element_type'] === 'field') {
                $ids['champs'][] = $element['id'];
            } elseif ($element['element_type'] === 'section') {
                $ids['sections'][] = $element['id'];
            }
        }

        // Si c'est une section, traiter récursivement tous les éléments enfants
        if ($element['element_type'] === 'section' && isset($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $childElement) {
                $this->collectElementIds($childElement, $ids);
            }
        }
    }

    /**
     * Traiter les données du formulaire avec mise à jour intelligente
     */
    private function processFormsDataWithUpdate($document, array $formsData, array $payloadIds, $parentSection = null): void
    {
        foreach ($formsData as $element) {
            $this->processFormElementWithUpdate($document, $element, $payloadIds, $parentSection);
        }
    }

    /**
     * Traiter un élément avec logique de création/mise à jour
     */
    private function processFormElementWithUpdate($document, array $element, array $payloadIds, $parentSection = null): void
    {
        if ($element['element_type'] === 'field') {
            $this->createOrUpdateField($document, $element, $parentSection);
        } elseif ($element['element_type'] === 'section') {
            $section = $this->createOrUpdateSection($document, $element, $parentSection);

            // Traiter récursivement les éléments enfants
            if (isset($element['elements']) && is_array($element['elements'])) {
                $this->processFormsDataWithUpdate($document, $element['elements'], $payloadIds, $section);
            }
        }
    }

    /**
     * Créer ou mettre à jour un champ
     */
    private function createOrUpdateField($document, array $fieldData, $section = null): void
    {
        $champAttributes = [
            'label' => $fieldData['label'],
            'info' => $fieldData['info'] ?? '',
            'attribut' => $fieldData['attribut'],
            'placeholder' => $fieldData['placeholder'] ?? '',
            'is_required' => $fieldData['is_required'] ?? false,
            'default_value' => $fieldData['default_value'] ?? null,
            'isEvaluated' => $fieldData['isEvaluated'] ?? false,
            'ordre_affichage' => $fieldData['ordre_affichage'],
            'type_champ' => $fieldData['type_champ'],
            'meta_options' => $fieldData['meta_options'] ?? [],
            'champ_standard' => $fieldData['champ_standard'] ?? false,
            'startWithNewLine' => $fieldData['startWithNewLine'] ?? null,
            'documentId' => $document->id,
            'sectionId' => $section ? $section->id : null
        ];

        if (isset($fieldData['id']) && $fieldData['id']) {

            // Mise à jour d'un champ existant
            $champ = $document->all_champs()->find($fieldData['id']);
            if ($champ) {
                $champ->update($champAttributes);
            } else {
                // L'ID n'existe pas, créer un nouveau champ
                $this->createOrUpdateChamp($champAttributes, $document, $section);
            }
        } else {
            // Création d'un nouveau champ
            $this->createOrUpdateChamp($champAttributes, $document, $section);
        }
    }

    /**
     * Créer ou mettre à jour une section
     */
    private function createOrUpdateSection($document, array $sectionData, $parentSection = null)
    {
        $sectionAttributes = [
            'intitule' => $sectionData['intitule'],
            'description' => $sectionData['description'] ?? '',
            'ordre_affichage' => $sectionData['ordre_affichage'],
            'type' => $sectionData['type'] ?? 'formulaire',
            'slug' => $sectionData['key'] ?? \Illuminate\Support\Str::slug($sectionData['intitule']),
            'parentSectionId' => $parentSection ? $parentSection->id : null
        ];

        if (isset($sectionData['id']) && $sectionData['id']) {
            // Mise à jour d'une section existante
            $section = $document->sections()->find($sectionData['id']);
            if ($section) {
                $section->update($sectionAttributes);
                return $section;
            } else {
                // L'ID n'existe pas, créer une nouvelle section
                return $document->sections()->create($sectionAttributes);
            }
        } else {
            // Création d'une nouvelle section
            return $document->sections()->create($sectionAttributes);
        }
    }

    /**
     * Supprimer les éléments qui ne sont plus présents dans le payload
     */
    private function cleanupRemovedElements($document, array $payloadIds): void
    {
        // Supprimer les champs non présents
        $document->champs()
            ->whereNotIn('id', $payloadIds['champs'])
            ->forceDelete();

        // Supprimer les sections non présentes
        $document->sections()
            ->whereNotIn('id', $payloadIds['sections'])
            ->forceDelete();
    }

    /** Etude de Prefaisabilite */

    public function canevasChecklistSuiviRapportPrefaisabilite(): JsonResponse
    {
        try {
            // Récupérer le canevas de checklist suivi rapport préfaisabilité unique
            $canevas = $this->repository->getCanevasChecklistSuiviRapportPrefaisabilite();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de checklist suivi rapport préfaisabilité trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de checklist suivi rapport préfaisabilité récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistSuiviRapportPrefaisabilite();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-suivi-rapport-prefaisabilite')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistSuiviRapportPrefaisabilite();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de checklist suivi rapport préfaisabilité mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-checklist-suivi-rapport-prefaisabilite";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistSuiviRapportPrefaisabilite();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de checklist suivi rapport préfaisabilité créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasChecklistMesuresAdaptation(): JsonResponse
    {
        try {
            // Récupérer le canevas de checklist mesures adaptation unique
            $canevas = $this->repository->getCanevasChecklistMesuresAdaptation();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de checklist mesures adaptation trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de checklist mesures adaptation récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
    public function createOrUpdateCanevasChecklistMesuresAdaptation(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistMesuresAdaptation();
            $data['categorieId'] = CategorieDocument::where('slug', 'checklist-mesures-adaptation-haut-risque')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistMesuresAdaptation();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de checklist mesures adaptation mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "checklist-mesures-adaptation-haut-risque";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistMesuresAdaptation();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de checklist mesures adaptation créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /** Etude de Faisabilite */

    public function canevasChecklistEtudeFaisabiliteMarche(): JsonResponse
    {
        try {
            // Récupérer le canevas de checklist etude faisabilite marche unique
            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteMarche();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de checklist etude faisabilite marche trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de checklist etude faisabilite marche récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteMarche(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteMarche();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-etude-faisabilite-marche')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteMarche();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de checklist etude faisabilite marche mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-checklist-etude-faisabilite-marche";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistEtudeFaisabiliteMarche();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de checklist etude faisabilite marche créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function getCanevasChecklistEtudeFaisabiliteEconomique()
    {
        try {
            // Récupérer le canevas de note conceptuelle unique
            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteEconomique();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction de note conceptuelle trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de note conceptuelle récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteEconomique(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteEconomique();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-etude-faisabilite-economique')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteEconomique();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de checklist etude faisabilite economique mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-checklist-etude-faisabilite-economique";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistEtudeFaisabiliteEconomique();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de checklist etude faisabilite economique créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasChecklistEtudeFaisabiliteTechnique(): JsonResponse
    {
        try {
            // Récupérer le canevas de checklist etude faisabilite technique unique
            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteTechnique();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de checklist etude faisabilite technique trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de checklist etude faisabilite technique récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteTechnique(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteTechnique();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-etude-faisabilite-technique')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteTechnique();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de note conceptuelle mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-appreciation-tdr";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistEtudeFaisabiliteTechnique();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de note conceptuelle créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasChecklistEtudeFaisabiliteFinanciere(): JsonResponse
    {
        try {
            // Récupérer le canevas de note conceptuelle unique
            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteFinanciere();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction de note conceptuelle trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de note conceptuelle récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteFinanciere(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteFinanciere();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-analyse-faisabilite-financiere')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteFinanciere();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de note conceptuelle mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-appreciation-tdr";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistEtudeFaisabiliteFinanciere();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de note conceptuelle créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique(): JsonResponse
    {
        try {
            // Récupérer le canevas de note conceptuelle unique
            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction de note conceptuelle trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de note conceptuelle récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-etude-faisabilite-organisationnelle-juridique')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de note conceptuelle mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-appreciation-tdr";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de note conceptuelle créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasChecklistEtudeAnalyseImpactEnvironnementalEtSociale(): JsonResponse
    {
        try {
            // Récupérer le canevas de note conceptuelle unique
            $canevas = $this->repository->getCanevasChecklistEtudeImpactEnvironnementalEtSociale();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction de note conceptuelle trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de note conceptuelle récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistEtudeAnalyseImpactEnvironnementalEtSociale(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistEtudeImpactEnvironnementalEtSociale();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-etude-faisabilite-environnemental-sociale')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistEtudeImpactEnvironnementalEtSociale();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de note conceptuelle mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-appreciation-tdr";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistEtudeImpactEnvironnementalEtSociale();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de note conceptuelle créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasChecklistSuiviAssuranceQualiteEtudeFaisabilite(): JsonResponse
    {
        try {
            // Récupérer le canevas de note conceptuelle unique
            $canevas = $this->repository->getCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction de note conceptuelle trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de note conceptuelle récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-checklist-suivi-assurance-qualite-etude-faisabilite')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de note conceptuelle mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-appreciation-tdr";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de note conceptuelle créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasAppreciationTDR(): JsonResponse
    {
        try {
            // Récupérer le canevas de note conceptuelle unique
            $canevas = $this->repository->getCanevasAppreciationTdr();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction de note conceptuelle trouvé.'
                ], 404);
            }

            return (new CanevasAppreciationTdrResource($canevas))
                ->additional(['message' => 'Canevas de note conceptuelle récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasAppreciationTDR(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasAppreciationTdr();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-appreciation-tdr')->firstOrFail()->id;

            if ($canevas) {
                unset($data["slug"]);
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                $canevas->fill($documentData);
                $canevas->save();
                $canevas->refresh();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);
                }

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // DÉSACTIVÉ temporairement pour éviter de supprimer les nouveaux champs
                // $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                $canevas->refresh();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasAppreciationTdr();

                return (new CanevasAppreciationTdrResource($canevas))
                    ->additional(['message' => 'Canevas de note conceptuelle mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                $data["slug"] = "canevas-appreciation-tdr";
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                if (isset($data['options_notation'])) {

                    $documentData['evaluation_configs']['options_notation'] = $data['options_notation'];
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasAppreciationTdr();

                return (new CanevasAppreciationTdrResource($document))
                    ->additional(['message' => 'Canevas de note conceptuelle créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasRedactionTdrPrefaisabilite(): JsonResponse
    {
        try {
            // Récupérer le canevas de rédaction TDR préfaisabilité unique
            $canevas = $this->repository->getCanevasRedactionTdrPrefaisabilite();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction TDR préfaisabilité trouvé.'
                ], 404);
            }

            return (new CanevasRedactionTdrPrefaisabiliteResource($canevas))
                ->additional(['message' => 'Canevas de rédaction TDR préfaisabilité récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasRedactionTdrPrefaisabilite(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasRedactionTdrPrefaisabilite();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-tdr-prefaisabilite')->firstOrFail()->id;

            if ($canevas) {
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();
                unset($documentData["evaluation_configs"]);
                unset($documentData["options_notation"]);
                $canevas->update($documentData);
                $canevas->refresh();

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // Supprimer les éléments non présents dans le payload
                $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasRedactionTdrPrefaisabilite();

                return (new CanevasRedactionTdrPrefaisabiliteResource($canevas))
                    ->additional(['message' => 'Canevas de rédaction TDR préfaisabilité mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                unset($documentData["evaluation_configs"]);
                unset($documentData["options_notation"]);

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasRedactionTdrPrefaisabilite();

                return (new CanevasRedactionTdrPrefaisabiliteResource($document))
                    ->additional(['message' => 'Canevas de rédaction TDR préfaisabilité créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function configurerChecklistTdrPrefaisabilite(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasRedactionTdrPrefaisabilite();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-tdr-prefaisabilite')->firstOrFail()->id;

            if ($canevas) {
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);

                    DB::commit();
                    $canevas->refresh();
                }

                // Recharger avec relations
                $canevas = $this->repository->getCanevasRedactionTdrPrefaisabilite();

                return (new CanevasRedactionTdrPrefaisabiliteResource($canevas))
                    ->additional(['message' => 'Canevas de rédaction TDR préfaisabilité mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                // Mode création
                $documentData = collect($data)->except(['forms'])->toArray();

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $documentData = array_merge($documentData, ['evaluation_configs' => $evaluationConfigs]);
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasRedactionTdrPrefaisabilite();

                return (new CanevasRedactionTdrPrefaisabiliteResource($document))
                    ->additional(['message' => 'Canevas de rédaction TDR préfaisabilité créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function canevasRedactionTdrFaisabilite(): JsonResponse
    {
        try {
            // Récupérer le canevas de rédaction TDR faisabilité unique
            $canevas = $this->repository->getCanevasRedactionTdrFaisabilite();

            if (!$canevas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun canevas de rédaction TDR faisabilité trouvé.'
                ], 404);
            }

            return (new CanevasRedactionTdrFaisabiliteResource($canevas))
                ->additional(['message' => 'Canevas de rédaction TDR faisabilité récupéré avec succès.'])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function createOrUpdateCanevasRedactionTdrFaisabilite(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasRedactionTdrFaisabilite();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-tdr-faisabilite')->firstOrFail()->id;

            if ($canevas) {
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                unset($documentData["evaluation_configs"]);
                unset($documentData["options_notation"]);

                $canevas->update($documentData);
                $canevas->refresh();

                // Collecter tous les IDs présents dans le payload
                $payloadIds = $this->collectAllIds($data['forms'] ?? []);

                // Traiter la structure forms avec mise à jour intelligente
                $this->processFormsDataWithUpdate($canevas, $data['forms'] ?? [], $payloadIds);

                // Supprimer les éléments non présents dans le payload
                $this->cleanupRemovedElements($canevas, $payloadIds);

                DB::commit();

                // Recharger avec relations
                $canevas = $this->repository->getCanevasRedactionTdrFaisabilite();

                return (new CanevasRedactionTdrFaisabiliteResource($canevas))
                    ->additional(['message' => 'Canevas de rédaction TDR faisabilité mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                // Mode création
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                unset($documentData["evaluation_configs"]);
                unset($documentData["options_notation"]);

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasRedactionTdrFaisabilite();

                return (new CanevasRedactionTdrFaisabiliteResource($document))
                    ->additional(['message' => 'Canevas de rédaction TDR faisabilité créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function configurerChecklistTdrFaisabilite(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $canevas = $this->repository->getCanevasRedactionTdrFaisabilite();
            $data['categorieId'] = CategorieDocument::where('slug', 'canevas-tdr-faisabilite')->firstOrFail()->id;

            if ($canevas) {
                // Mode mise à jour intelligente
                $documentData = collect($data)->except(['forms', 'id'])->toArray();

                // Récupérer la configuration existante ou créer une nouvelle
                $evaluationConfigs = $canevas->evaluation_configs ?? [];

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $canevas->update(['evaluation_configs' => $evaluationConfigs]);

                    DB::commit();
                    $canevas->refresh();
                }

                // Recharger avec relations
                $canevas = $this->repository->getCanevasRedactionTdrFaisabilite();

                return (new CanevasRedactionTdrFaisabiliteResource($canevas))
                    ->additional(['message' => 'Canevas de rédaction TDR préfaisabilité mis à jour avec succès.'])
                    ->response()
                    ->setStatusCode(200);
            } else {
                // Mode création
                $documentData = collect($data)->except(['forms'])->toArray();

                if (isset($data['options_notation'])) {

                    // Mettre à jour les options de notation
                    $evaluationConfigs['options_notation'] = $data['options_notation'];

                    // Sauvegarder la configuration
                    $documentData = array_merge($documentData, ['evaluation_configs' => $evaluationConfigs]);
                }

                $document = $this->repository->create($documentData);

                // Traiter la structure forms (création)
                $this->processFormsData($document, $data['forms'] ?? []);

                // Générer et sauvegarder la structure JSON
                $this->structureService->generateAndSaveStructure($document);

                DB::commit();

                // Recharger avec relations
                $document = $this->repository->getCanevasRedactionTdrFaisabilite();

                return (new CanevasRedactionTdrFaisabiliteResource($document))
                    ->additional(['message' => 'Canevas de rédaction TDR préfaisabilité créé avec succès.'])
                    ->response()
                    ->setStatusCode(201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }
}
