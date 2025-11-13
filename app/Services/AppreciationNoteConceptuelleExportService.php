<?php

namespace App\Services;

use App\Models\Projet;
use App\Models\NoteConceptuelle;
use Illuminate\Support\Facades\Storage;

class AppreciationNoteConceptuelleExportService
{
    /**
     * Exporter l'appréciation de la note conceptuelle vers Excel
     */
    public function export(Projet $projet): array
    {
        $noteConceptuelle = $projet->noteConceptuelle;

        if (!$noteConceptuelle) {
            throw new \Exception("Le projet n'a pas de note conceptuelle");
        }

        // Récupérer le canevas et les données d'évaluation
        $canevas = $noteConceptuelle->canevas_appreciation_note_conceptuelle;

        // Récupérer l'évaluation terminée ou en cours
        $evaluationModel = $noteConceptuelle->evaluationTermine() ?? $noteConceptuelle->evaluationEnCours();

        // Si l'évaluation existe, récupérer les données via la propriété 'evaluation'
        // qui contient un tableau avec la clé 'champs_evalues'
        $evaluations = [];
        if ($evaluationModel) {
            $evaluationData = $evaluationModel->evaluation ?? [];

            if (!empty($evaluationData['champs_evalues'])) {
                foreach ($evaluationData['champs_evalues'] as $champ) {
                    $evaluations[$champ['attribut']] = [
                        'commentaire' => $champ['commentaire_evaluateur'] ?? '',
                        'appreciation' => $champ['appreciation'] ?? '',
                    ];
                }
            }
        }

        if (!$canevas) {
            throw new \Exception("Aucun canevas d'appréciation trouvé");
        }

        // Préparer les données pour le script Python
        $data = $this->prepareDataForExport($projet, $noteConceptuelle, $canevas, $evaluations);

        // Chemins des fichiers
        $templatePath = base_path('canevas/O-5_Template_Appreciation_Clean.xlsx');
        $pythonScript = base_path('scripts/generate_appreciation_excel.py');

        if (!file_exists($templatePath)) {
            throw new \Exception("Le template Excel n'existe pas: {$templatePath}");
        }

        if (!file_exists($pythonScript)) {
            throw new \Exception("Le script Python n'existe pas: {$pythonScript}");
        }

        // Générer le nom du fichier
        $fileName = 'appreciation_note_conceptuelle_' . $projet->identifiant_bip . '.xlsx';
        // Hasher l'identifiant BIP pour le stockage
        $hashedIdentifiantBip = hash('sha256', $projet->identifiant_bip);
        $relativePath = "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle";

        // Créer les répertoires si nécessaire
        $fullPath = Storage::disk('local')->path($relativePath . '/' . $fileName);
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        // Créer un fichier JSON temporaire avec les données
        $jsonPath = Storage::disk('local')->path('temp_appreciation_data_' . $projet->id . '.json');
        file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        try {
            // Exécuter le script Python
            $command = sprintf(
                'python3 %s %s %s %s 2>&1',
                escapeshellarg($pythonScript),
                escapeshellarg($jsonPath),
                escapeshellarg($templatePath),
                escapeshellarg($fullPath)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("Erreur lors de l'exécution du script Python: " . implode("\n", $output));
            }

            // Supprimer le fichier JSON temporaire
            @unlink($jsonPath);

            // Enregistrer dans la base de données
            $fileSize = filesize($fullPath);
            $md5Hash = md5_file($fullPath);

            $fichier = $noteConceptuelle->fichiers()->create([
                'nom_original' => $fileName,
                'nom_stockage' => $fileName,
                'chemin' => $relativePath . '/' . $fileName,
                'taille' => $fileSize,
                'extension' => 'xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'md5_hash' => $md5Hash,
                'categorie' => 'appreciation_note_conceptuelle',
                'uploaded_by' => 1, // System user
            ]);

            return [
                'fichier' => $fichier,
                'path' => $relativePath,
                'full_path' => $fullPath,
                'size' => $fileSize,
                'md5' => $md5Hash,
            ];
        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            @unlink($jsonPath);
            throw $e;
        }
    }

    /**
     * Préparer les données pour l'export Excel (format JSON)
     */
    private function prepareDataForExport(Projet $projet, NoteConceptuelle $noteConceptuelle, array $canevas, array $evaluations): array
    {
        // Récupérer les informations du proposant/responsable
        $responsable = $projet->responsable;
        $ministere = $projet->ministere;

        // Coût du projet
        if (!empty($projet->cout_estimatif_projet)) {
            $coutEstimatif = is_array($projet->cout_estimatif_projet)
                ? ($projet->cout_estimatif_projet['montant'] ?? '')
                : $projet->cout_estimatif_projet;

        }

        $data = [
            'header' => [
                'titre_projet' => $projet->titre_projet ?? '',
                'identifiant_bip' => $projet->identifiant_bip ?? '',
                'cout_total' => $coutEstimatif ? number_format($coutEstimatif, 0, ',', ' ') . ' FCFA' : '',
                'date_demarrage' => $noteConceptuelle->date_demarrage_etude ?? '',
                'date_achevement' => $noteConceptuelle->date_achevement_etude ?? '',
            ],
            'accept_text' => $canevas['evaluation_configs']['accept_text'] ?? '',
            'proposant' => [
                'nom' => $responsable ? ($responsable->personne->nom . ' ' . $responsable->personne->prenom) : '',
                'telephone' => $responsable?->telephone ?? '',
                'email' => $responsable->email ?? '',
                'ministere' => $ministere->nom ?? '',
            ],
            'elements' => [],
        ];

        // Parcourir le canevas pour extraire les sections et questions
        $forms = $canevas['forms'] ?? [];

        foreach ($forms as $form) {
            if ($form['element_type'] === 'section') {
                // Ajouter la section
                $data['elements'][] = [
                    'type' => 'section',
                    'title' => $form['intitule'],
                ];

                // Ajouter les questions de cette section
                if (!empty($form['elements'])) {
                    foreach ($form['elements'] as $element) {
                        if ($element['element_type'] === 'field' && ($element['isEvaluated'] ?? false)) {
                            // Récupérer l'évaluation pour ce champ
                            $evaluation = $evaluations[$element['attribut']] ?? null;
                            $commentaire = $evaluation['commentaire'] ?? '';
                            $appreciation = $evaluation['appreciation'] ?? '';

                            // Construire le guide de notation à partir des options
                            $options = $element['meta_options']['configs']['options'] ?? [];
                            $guide = [];
                            foreach ($options as $option) {
                                $guide[] = ucfirst($option['label']) . ': ' . $option['description'];
                            }

                            $data['elements'][] = [
                                'type' => 'question',
                                'title' => $element['label'],
                                'comment' => $commentaire,
                                'appreciation' => $appreciation,
                                'guide' => implode("\n", $guide),
                            ];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Remplir les informations d'en-tête
     */
    private function fillHeaderInfo(Worksheet $sheet, Projet $projet, NoteConceptuelle $noteConceptuelle): void
    {
        // Titre du projet (ligne 4, colonne B)
        $sheet->setCellValue('B4', $projet->titre ?? '');

        // Numéro BIP (ligne 5, colonne B)
        $sheet->setCellValue('B5', $projet->identifiant_bip ?? '');

        // Coût du projet (ligne 6, colonne B)
        $sheet->setCellValue('B6', $projet->cout_total ?? '');

        // Date de démarrage (ligne 7, colonne B)
        $sheet->setCellValue('B7', $noteConceptuelle->date_demarrage_etude ?? '');

        // Date d'achèvement (ligne 8, colonne B)
        $sheet->setCellValue('B8', $noteConceptuelle->date_achevement_etude ?? '');
    }

    /**
     * Préparer toutes les lignes de données en batch (RAPIDE!)
     */
    private function prepareRowsData(array $forms, array $evaluations): array
    {
        $rows = [];

        foreach ($forms as $form) {
            if ($form['element_type'] === 'section') {
                // Ajouter la ligne de section
                $rows[] = [
                    'content' => $form['intitule'],
                    'type' => 'section',
                    'data' => [],
                ];

                // Ajouter les questions de cette section
                if (!empty($form['elements'])) {
                    foreach ($form['elements'] as $element) {
                        if ($element['element_type'] === 'field' && ($element['isEvaluated'] ?? false)) {
                            $evaluation = $evaluations[$element['attribut']] ?? null;
                            $statut = $evaluation['statut'] ?? '';
                            $commentaire = $evaluation['commentaire'] ?? '';

                            // Légende des options
                            $options = $element['meta_options']['configs']['options'] ?? [];
                            $legend = [];
                            foreach ($options as $option) {
                                $legend[] = ucfirst($option['label']) . ': ' . $option['description'];
                            }

                            $rows[] = [
                                'content' => $element['label'],
                                'type' => 'question',
                                'data' => [
                                    'statut' => $statut ? ucfirst($statut) : '',
                                    'commentaire' => $commentaire,
                                    'legend' => implode("\n\n", $legend),
                                ],
                            ];
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * Appliquer les styles aux lignes (RAPIDE!)
     */
    private function applyRowsStyles(Worksheet $sheet, array $rows, int $startRow): void
    {
        $currentRow = $startRow;

        foreach ($rows as $row) {
            if ($row['type'] === 'section') {
                // Style pour les sections
                $sheet->getStyle("A{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->mergeCells("A{$currentRow}:E{$currentRow}");

            } else if ($row['type'] === 'question') {
                // Question wrap text
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setWrapText(true);

                // Validation field avec style jaune
                $sheet->setCellValue("C{$currentRow}", "[ Valider le statut ]");
                $sheet->getStyle("C{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFD966'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Statut et commentaire
                if (!empty($row['data']['statut'])) {
                    $sheet->setCellValue("D{$currentRow}", $row['data']['statut']);
                }
                if (!empty($row['data']['commentaire'])) {
                    $sheet->setCellValue("E{$currentRow}", $row['data']['commentaire']);
                    $sheet->getStyle("E{$currentRow}")->getAlignment()->setWrapText(true);
                }

                // Légende
                if (!empty($row['data']['legend'])) {
                    $sheet->setCellValue("F{$currentRow}", $row['data']['legend']);
                    $sheet->getStyle("F{$currentRow}")->getAlignment()->setWrapText(true);
                }

                // Hauteur auto
                $sheet->getRowDimension($currentRow)->setRowHeight(-1);
            }

            $currentRow++;
        }
    }

    /**
     * Ajouter dynamiquement les sections et questions (DEPRECATED - trop lent)
     */
    private function addSectionsAndQuestions(Worksheet $sheet, array $forms, array $evaluations, int $startRow): int
    {
        $currentRow = $startRow;

        foreach ($forms as $form) {
            if ($form['element_type'] === 'section') {
                // Ajouter la section principale
                $currentRow = $this->addSection($sheet, $form, $currentRow);

                // Ajouter les champs/questions de cette section
                if (!empty($form['elements'])) {
                    foreach ($form['elements'] as $element) {
                        if ($element['element_type'] === 'field' && ($element['isEvaluated'] ?? false)) {
                            $currentRow = $this->addQuestion($sheet, $element, $evaluations, $currentRow);
                        }
                    }
                }
            }
        }

        return $currentRow;
    }

    /**
     * Ajouter une section
     */
    private function addSection(Worksheet $sheet, array $section, int $row): int
    {
        // Insérer une nouvelle ligne
        $sheet->insertNewRowBefore($row, 1);

        // Titre de la section en colonne A avec style (Bold + Background)
        $sheet->setCellValue("A{$row}", $section['intitule']);

        // Appliquer le style de section
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Fusionner les colonnes A à E pour la section
        $sheet->mergeCells("A{$row}:E{$row}");

        return $row + 1;
    }

    /**
     * Ajouter une question
     */
    private function addQuestion(Worksheet $sheet, array $field, array $evaluations, int $row): int
    {
        // Insérer une nouvelle ligne
        $sheet->insertNewRowBefore($row, 1);

        // Question en colonne A
        $sheet->setCellValue("A{$row}", $field['label']);
        $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true);

        // Récupérer l'évaluation pour ce champ
        $evaluation = $evaluations[$field['attribut']] ?? null;
        $statut = $evaluation['statut'] ?? '';
        $commentaire = $evaluation['commentaire'] ?? '';

        // Champ de validation en colonne C
        $sheet->setCellValue("C{$row}", "[ Valider le statut ]");
        $sheet->getStyle("C{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD966'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Statut sélectionné en colonne D
        if ($statut) {
            $sheet->setCellValue("D{$row}", ucfirst($statut));
        }

        // Commentaire en colonne E
        if ($commentaire) {
            $sheet->setCellValue("E{$row}", $commentaire);
            $sheet->getStyle("E{$row}")->getAlignment()->setWrapText(true);
        }

        // Légende des options en colonne F
        $options = $field['meta_options']['configs']['options'] ?? [];
        $legend = [];
        foreach ($options as $option) {
            $legend[] = ucfirst($option['label']) . ': ' . $option['description'];
        }
        $sheet->setCellValue("F{$row}", implode("\n\n", $legend));
        $sheet->getStyle("F{$row}")->getAlignment()->setWrapText(true);

        // Hauteur de ligne automatique
        $sheet->getRowDimension($row)->setRowHeight(-1);

        return $row + 1;
    }

    /**
     * Ajouter la section de pied de page (signature et résultats)
     */
    private function addFooterSection(Worksheet $sheet, int $startRow): void
    {
        $row = $startRow + 2;

        // Section "À remplir par le proposant du projet"
        $sheet->setCellValue("A{$row}", "À remplir par le proposant du projet");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);
        $sheet->mergeCells("A{$row}:E{$row}");

        $row++;
        $sheet->setCellValue("A{$row}", "Proposition de projet préparée par (Nom) :");

        $row++;
        $sheet->setCellValue("A{$row}", "Nom du ministère :");

        $row += 2;

        // Section "Résultats de l'examen"
        $sheet->setCellValue("A{$row}", "Résultats de l'examen");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);
        $sheet->mergeCells("A{$row}:E{$row}");

        $row++;
        $sheet->setCellValue("A{$row}", "Nombre de rubriques validées");
        $sheet->setCellValue("B{$row}", "=COUNTIF(D14:D100,\"Validé\")");

        $row++;
        $sheet->setCellValue("A{$row}", "Nombre de rubriques ayant fait objet de réserve");
        $sheet->setCellValue("B{$row}", "=COUNTIF(D14:D100,\"Réservé\")");

        $row++;
        $sheet->setCellValue("A{$row}", "Nombre de rubriques rejetées");
        $sheet->setCellValue("B{$row}", "=COUNTIF(D14:D100,\"Rejeté\")");
    }
}
