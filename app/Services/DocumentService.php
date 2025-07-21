<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\DocumentResource;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\Contracts\DocumentServiceInterface;

class DocumentService extends BaseService implements DocumentServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        DocumentRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
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
            $document->load(['sections.champs', 'champs']);

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
            'isEvaluated' => $champData['isEvaluated'] ?? false,
            'default_value' => $champData['default_value'] ?? null,
            'commentaire' => $champData['commentaire'] ?? null,
            'ordre_affichage' => $champData['ordre_affichage'],
            'type_champ' => $champData['type_champ'],
            'meta_options' => $champData['meta_options'] ?? [],
            'champ_config' => $champData['champ_config'] ?? [],
            'valeur_config' => $champData['valeur_config'] ?? [],
            'documentId' => $document->id,
            'secteurId' => $section ? $section->id : null
        ];

        // Créer le champ via la relation appropriée
        if ($section) {
            $section->champs()->create($champAttributes);
        } else {
            $document->champs()->create($champAttributes);
        }
    }
}