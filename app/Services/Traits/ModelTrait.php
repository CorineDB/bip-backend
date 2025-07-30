<?php

namespace App\Services\Traits;

use Illuminate\Database\Eloquent\Model;

trait ModelTrait
{


    /**
     * Initialiser la structure de base de ficheIdee pour une nouvelle idée
     */
    private function initializeFicheIdeeStructure(): array
    {
        // Récupérer la fiche idée (document formulaire)
        $ficheIdee = \App\Models\Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'fiche-idee');
        })
            ->where('type', 'formulaire')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$ficheIdee) {
            return [];
        }

        // Structure de base avec les informations de la fiche
        $ficheIdeeStructure = [
            'document_id' => $ficheIdee->id,
            'document_nom' => $ficheIdee->nom ?? 'Fiche Idée de Projet',
            'document_version' => $ficheIdee->version ?? '1.0',
            'date_creation' => now()->toISOString(),
            'date_remplissage' => null,
            'sections' => [],
            'champs_values' => [],
            'relations_values' => []
        ];

        // Organiser les sections avec champs vides
        foreach ($ficheIdee->sections as $section) {
            $sectionData = [
                'id' => $section->id,
                'nom' => $section->nom,
                'ordre' => $section->ordre,
                'champs' => []
            ];

            foreach ($section->champs as $champ) {
                $champData = [
                    'id' => $champ->id,
                    'nom' => $champ->nom,
                    'label' => $champ->label,
                    'type_champ' => $champ->type_champ,
                    'attribut' => $champ->attribut,
                    'required' => $champ->meta_options['validations_rules']['required'] ?? false,
                    'ordre' => $champ->ordre,
                    'valeur' => null, // Vide à l'initialisation
                    'valeur_attribut' => null, // Vide à l'initialisation
                    'relations' => [] // Vide à l'initialisation
                ];

                $sectionData['champs'][] = $champData;

                // Ajouter à l'index global des valeurs (vides)
                $ficheIdeeStructure['champs_values'][$champ->id] = [
                    'attribut' => $champ->attribut,
                    'valeur' => null,
                    'valeur_attribut' => null,
                    'relations' => []
                ];
            }

            $ficheIdeeStructure['sections'][] = $sectionData;
        }

        // Ajouter les champs directs de la fiche (non organisés en sections)
        if ($ficheIdee->champs && $ficheIdee->champs->count() > 0) {
            $champsDirects = [
                'id' => 'champs_directs',
                'nom' => 'Champs Directs',
                'ordre' => 999,
                'champs' => []
            ];

            foreach ($ficheIdee->champs as $champ) {
                $champData = [
                    'id' => $champ->id,
                    'nom' => $champ->nom,
                    'label' => $champ->label,
                    'type_champ' => $champ->type_champ,
                    'attribut' => $champ->attribut,
                    'required' => $champ->meta_options['validations_rules']['required'] ?? false,
                    'ordre' => $champ->ordre,
                    'valeur' => null, // Vide à l'initialisation
                    'valeur_attribut' => null, // Vide à l'initialisation
                    'relations' => [] // Vide à l'initialisation
                ];

                $champsDirects['champs'][] = $champData;
                $ficheIdeeStructure['champs_values'][$champ->id] = [
                    'attribut' => $champ->attribut,
                    'valeur' => null,
                    'valeur_attribut' => null,
                    'relations' => []
                ];
            }

            if (!empty($champsDirects['champs'])) {
                $ficheIdeeStructure['sections'][] = $champsDirects;
            }
        }

        // Ajouter des métadonnées de complétion (vides à l'initialisation)
        $ficheIdeeStructure['metadata'] = [
            'total_champs' => count($ficheIdeeStructure['champs_values']),
            'champs_remplis' => 0,
            'taux_completion' => 0,
            'relations_count' => [],
            'created_at' => now()->toISOString(),
            'last_updated' => null
        ];

        return $ficheIdeeStructure;
    }
}