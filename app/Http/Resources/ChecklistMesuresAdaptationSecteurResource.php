<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ChecklistMesuresAdaptationSecteurResource extends BaseApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'slug' => $this->slug,
            'is_mandatory' => $this->is_mandatory,
            'description' => 'Checklist des mesures d\'adaptation pour les projets à haut risque climatique',

            // Informations générales sur la checklist
            'metadata' => [
                'total_criteres' => $this->whenLoaded('criteres', function () {
                    return $this->criteres->count();
                }),
                'total_ponderation' => $this->whenLoaded('criteres', function () {
                    return $this->criteres->sum('ponderation');
                }),
                'secteurs_couverts' => $this->whenLoaded('criteres', function () {
                    return $this->criteres
                        ->flatMap(function ($critere) {
                            return $critere->notations->pluck('secteur.nom')->filter();
                        })
                        ->unique()
                        ->values();
                })
            ],

            // Structure détaillée des critères et mesures d'adaptation
            'criteres' => $this->whenLoaded('criteres', function () {
                return $this->criteres->map(function ($critere) {
                    return [
                        'id' => $critere->id,
                        'intitule' => $critere->intitule,
                        'description' => $critere->commentaire,
                        'ponderation' => $critere->ponderation,
                        'ponderation_pct' => $critere->ponderation . '%',
                        'is_mandatory' => $critere->is_mandatory,

                        // Mesures d'adaptation par secteur
                        'mesures' => $critere->notations->map(function ($notation) {
                            return [
                                'id' => $notation->id,
                                'libelle' => $notation->libelle,
                                'valeur' => $notation->valeur,
                                'description' => $notation->commentaire
                            ];
                        })->values()
                    ];
                });
            }),

            // Instructions d'utilisation
            'instructions' => [
                'titre' => 'Comment utiliser cette checklist',
                'etapes' => [
                    '1. Identifier le secteur principal de votre projet',
                    '2. Parcourir chaque critère d\'adaptation',
                    '3. Sélectionner les mesures appropriées pour chaque critère',
                    '4. Documenter comment ces mesures seront implémentées',
                    '5. Valider la complétude de votre plan d\'adaptation'
                ],
                'notes' => [
                    'Cette checklist est obligatoire pour les projets à haut risque climatique',
                    'Certaines mesures peuvent être applicables à plusieurs secteurs',
                    'Le score total doit atteindre un seuil minimum pour validation'
                ]
            ],

            // Fichiers de référence joints
            'fichiers_reference' => $this->when($this->relationLoaded('fichiers'), function() {
                return $this->fichiers->map(function ($fichier) {
                    return [
                        'id' => $fichier->id,
                        'nom' => $fichier->nom_original,
                        'type' => $fichier->extension,
                        'taille' => $fichier->taille_formatee,
                        'url' => $fichier->url,
                        'description' => $fichier->description,
                        'categorie' => $fichier->categorie,
                        'date_ajout' => Carbon::parse($fichier->created_at)->format('d/m/Y')
                    ];
                });
            }),

            'created_at' => Carbon::parse($this->created_at)->format('d/m/Y'),
            'updated_at' => Carbon::parse($this->updated_at)->format('d/m/Y H:i:s')
        ];
    }

    /**
     * Catégoriser une mesure selon son libellé
     */
    private function categoriserMesure(string $libelle): string
    {
        $libelle = strtolower($libelle);

        if (str_contains($libelle, 'eau') || str_contains($libelle, 'irrigation') || str_contains($libelle, 'dessalement')) {
            return 'Gestion de l\'eau';
        }

        if (str_contains($libelle, 'refroidissement') || str_contains($libelle, 'chaleur') || str_contains($libelle, 'température')) {
            return 'Gestion thermique';
        }

        if (str_contains($libelle, 'végétale') || str_contains($libelle, 'sélection') || str_contains($libelle, 'culture')) {
            return 'Adaptation biologique';
        }

        if (str_contains($libelle, 'emplacement') || str_contains($libelle, 'site') || str_contains($libelle, 'localisation')) {
            return 'Optimisation spatiale';
        }

        if (str_contains($libelle, 'maintenance') || str_contains($libelle, 'exploitation') || str_contains($libelle, 'gestion')) {
            return 'Gestion opérationnelle';
        }

        if (str_contains($libelle, 'composant') || str_contains($libelle, 'matériel') || str_contains($libelle, 'équipement')) {
            return 'Adaptation technique';
        }

        return 'Autre';
    }

    /**
     * Obtenir la description d'un type de mesure
     */
    private function getDescriptionTypeMesure(string $type): string
    {
        $descriptions = [
            'Gestion de l\'eau' => 'Mesures liées à la conservation, distribution et utilisation efficace de l\'eau',
            'Gestion thermique' => 'Mesures de protection contre les températures extrêmes',
            'Adaptation biologique' => 'Mesures d\'adaptation des espèces végétales et pratiques agricoles',
            'Optimisation spatiale' => 'Mesures de choix stratégique d\'emplacement et d\'aménagement',
            'Gestion opérationnelle' => 'Mesures d\'amélioration des pratiques de gestion et maintenance',
            'Adaptation technique' => 'Mesures d\'amélioration des équipements et technologies',
            'Autre' => 'Autres mesures d\'adaptation'
        ];

        return $descriptions[$type] ?? 'Description non disponible';
    }

    /**
     * Obtenir les secteurs applicables pour une mesure donnée
     */
    private function getSecteursApplicablesPourMesure(string $libelle): array
    {
        // Cette méthode pourrait être enrichie avec une logique plus sophistiquée
        // basée sur une base de données ou des règles métier

        $libelle = strtolower($libelle);
        $secteurs = [];

        if (str_contains($libelle, 'irrigation') || str_contains($libelle, 'végétale') || str_contains($libelle, 'exploitation')) {
            $secteurs[] = 'Agriculture';
        }

        if (str_contains($libelle, 'éolien') || str_contains($libelle, 'solaire') || str_contains($libelle, 'refroidissement')) {
            $secteurs[] = 'Énergie';
        }

        if (str_contains($libelle, 'eau') || str_contains($libelle, 'dessalement') || str_contains($libelle, 'aquifère')) {
            $secteurs[] = 'Eau';
        }

        return array_unique($secteurs);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return array_merge(parent::with($request), [
            'meta' => [
                'type' => 'checklist-mesures-adaptation',
                'version' => '1.0',
                'format' => 'structured-checklist',
                'usage' => 'Projets à haut risque climatique',
            ],
        ]);
    }
}