<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\DocumentResource;
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
    )
    {
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
    private function createSectionsWithChamps($document, array $sectionsData): void
    {
        foreach ($sectionsData as $sectionData) {
            $section = $document->sections()->create([
                'intitule' => $sectionData['intitule'],
                'description' => $sectionData['description'],
                'ordre_affichage' => $sectionData['ordre_affichage'],
                'type' => $sectionData['type'] ?? null
            ]);

            // Créer les champs de cette section si fournis
            if (isset($sectionData['champs']) && is_array($sectionData['champs'])) {
                foreach ($sectionData['champs'] as $champData) {
                    $this->createChamp($champData, $document, $section);
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
     * Créer un champ avec validation des données
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

        // Créer le champ via la relation appropriée
        if ($section) {
            $section->champs()->create($champAttributes);
        } else {
            $document->champs()->create($champAttributes);
        }
    }

    /**
     * Mettre à jour les sections avec leurs champs pour un document existant
     */
    private function updateSectionsWithChamps($document, array $sectionsData)
    {
        foreach ($sectionsData as $sectionData) {
            $sectionId = $sectionData['id'] ?? null;
            $champsData = $sectionData['champs'] ?? [];

            // Extraire les données de la section (sans les champs)
            $sectionAttributes = collect($sectionData)->except(['id', 'champs'])->toArray();

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

            // Extraire les données du champ
            $champAttributes = collect($champData)->except(['id', 'sectionId'])->toArray();
            $champAttributes['sectionId'] = $section->id;

            if ($champId) {
                // Mettre à jour champ existant
                $champ = $section->champs()->find($champId);
                if ($champ) {
                    $champ->fill($champAttributes);
                    $champ->save();
                    //$champ->update($champAttributes);
                } else {
                    // Champ n'existe pas, le créer
                    $section->champs()->create($champAttributes);
                }
            } else {
                // Créer nouveau champ
                $section->champs()->create($champAttributes);
            }
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
                    $document->champs()->create($champAttributes);
                }
            } else {
                // Créer nouveau champ
                $document->champs()->create($champAttributes);
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
                $ficheIdee->update($documentData);//$this->repository->update($id, $documentData);

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
}
