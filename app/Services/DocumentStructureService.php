<?php

namespace App\Services;

use App\Models\Document;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\ChampResource;
use App\Http\Resources\ChampSectionResource;

class DocumentStructureService
{
    /**
     * Génère et sauvegarde automatiquement la structure JSON resource d'un document
     */
    public function generateAndSaveStructure(Document $document): array
    {
        $resource = new DocumentResource($document->load(['champs', 'sections.champs', 'categorie']));
        $structure = $resource->toArray(request());

        // Nettoyer la structure pour ne garder que les métadonnées essentielles
        //$cleanStructure = $this->cleanStructureForStorage($structure);

        // Sauvegarder la structure dans le document
        $document->update(['structure' => $structure]);

        return $structure;
    }

    /**
     * Nettoie la structure pour le stockage en ne gardant que les métadonnées importantes
     */
    private function cleanStructureForStorage(array $structure): array
    {
        return [
            'document_info' => [
                'id' => $structure['id'],
                'nom' => $structure['nom'],
                'type' => $structure['type'],
                'description' => $structure['description']
            ],
            'categorie' => isset($structure['categorie']) ? [
                'id' => $structure['categorie']['id'] ?? null,
                'nom' => $structure['categorie']['nom'] ?? null,
                'format' => $structure['categorie']['format'] ?? null
            ] : null,
            'sections_structure' => $this->extractSectionsStructure($structure['sections'] ?? []),
            'champs_structure' => $this->extractChampsStructure($structure['champs'] ?? []),
            'metadata' => $structure['metadata'] ?? null,
            'generated_at' => now()->toISOString(),
            'version' => '1.0'
        ];
    }

    /**
     * Extrait la structure des sections
     */
    private function extractSectionsStructure(array $sections): array
    {
        return array_map(function ($section) {
            return [
                'id' => $section['id'],
                'key' => $section['key'],
                'intitule' => $section['intitule'],
                'type' => $section['type'],
                'ordre_affichage' => $section['ordre_affichage'],
                'champs_count' => count($section['champs'] ?? []),
                'champs_keys' => array_column($section['champs'] ?? [], 'key')
            ];
        }, $sections);
    }

    /**
     * Extrait la structure des champs
     */
    private function extractChampsStructure(array $champs): array
    {
        return array_map(function ($champ) {
            return [
                'id' => $champ['id'],
                'key' => $champ['key'],
                'label' => $champ['label'],
                'type_champ' => $champ['type_champ'],
                'is_required' => $champ['is_required'],
                'ordre_affichage' => $champ['ordre_affichage'],
                'sectionId' => $champ['sectionId'],
                'champ_standard' => $champ['champ_standard'],
                'has_meta_options' => !empty($champ['meta_options'])
            ];
        }, $champs);
    }

    /**
     * Récupère la structure sauvegardée d'un document
     */
    public function getStoredStructure(Document $document): array
    {
        return $document->structure ?? [];
    }

    /**
     * Compare la structure actuelle avec celle stockée
     */
    public function hasStructureChanged(Document $document): bool
    {
        $currentStructure = $this->generateStructure($document);
        $storedStructure = $this->getStoredStructure($document);

        return json_encode($currentStructure) !== json_encode($storedStructure);
    }

    /**
     * Génère la structure sans la sauvegarder
     */
    public function generateStructure(Document $document): array
    {
        $resource = new DocumentResource($document->load(['champs', 'sections.champs', 'categorie']));
        $structure = $resource->toArray(request());

        return $this->cleanStructureForStorage($structure);
    }
}