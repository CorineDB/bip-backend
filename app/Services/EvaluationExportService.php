<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\IdeeProjet;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EvaluationExportService
{
    /**
     * Export l'évaluation de pertinence au format Excel
     */
    public function exportPertinenceToExcel(Evaluation $evaluation)
    {
        \Log::info("📊 [EvaluationExportService] Début export pertinence", [
            'evaluation_id' => $evaluation->id,
            'projetable_type' => $evaluation->projetable_type,
            'projetable_id' => $evaluation->projetable_id
        ]);

        // Charger le template Excel
        $templatePath = base_path('canevas/O-3_Évaluation de la pertinence_18-06-2025-rev MN.xlsx');

        \Log::info("📂 [EvaluationExportService] Vérification du template", [
            'template_path' => $templatePath,
            'exists' => file_exists($templatePath)
        ]);

        if (!file_exists($templatePath)) {
            \Log::error("❌ [EvaluationExportService] Template introuvable", [
                'template_path' => $templatePath
            ]);
            throw new \Exception("Template de canevas introuvable: {$templatePath}");
        }

        \Log::info("📄 [EvaluationExportService] Chargement du spreadsheet");

        // Charger le spreadsheet depuis le template
        $spreadsheet = IOFactory::load($templatePath);

        \Log::info("✅ [EvaluationExportService] Spreadsheet chargé");

        // Récupérer le projet
        $project = $evaluation->projetable;

        if (!$project instanceof IdeeProjet) {
            \Log::error("❌ [EvaluationExportService] Type de projet invalide", [
                'projetable_type' => get_class($project)
            ]);
            throw new \Exception("L'évaluation doit être liée à une IdeeProjet");
        }

        \Log::info("✅ [EvaluationExportService] Projet récupéré", [
            'project_id' => $project->id,
            'identifiant_bip' => $project->identifiant_bip,
            'titre' => $project->titre_projet
        ]);

        // Récupérer le canevas et l'évaluation depuis le projet
        $canevas = $project->canevas_appreciation_pertinence;
        $evaluationPertinence = $project->evaluationPertinence->first();

        if (!$evaluationPertinence) {
            \Log::error("❌ [EvaluationExportService] Aucune évaluation de pertinence", [
                'project_id' => $project->id
            ]);
            throw new \Exception("Aucune évaluation de pertinence trouvée pour ce projet");
        }

        \Log::info("📝 [EvaluationExportService] Remplissage des feuilles Excel", [
            'nb_sheets' => $spreadsheet->getSheetCount()
        ]);

        // Feuille 0: Page de couverture - Informations du projet et canevas
        $coverSheet = $spreadsheet->getSheet(0);
        $this->fillProjectInfo($coverSheet, $project);
        $this->fillCanevasInfo($coverSheet, $canevas);

        \Log::info("✅ [EvaluationExportService] Feuille 0 (couverture) remplie");

        // Feuille 1: PERTINENCE - Résultats de l'évaluation
        $resultSheet = $spreadsheet->getSheet(1);
        $this->fillPertinenceCriteres($resultSheet, $project, $evaluationPertinence);
        $this->fillAggregatedScores($resultSheet, $evaluationPertinence);

        \Log::info("✅ [EvaluationExportService] Feuille 1 (résultats) remplie");

        // Générer le nom de stockage
        $category = 'evaluation_pertinence';
        $extension = 'xlsx';
        $storageName = $this->generateStorageName($category, $project->identifiant_bip, $extension);

        \Log::info("💾 [EvaluationExportService] Sauvegarde du fichier", [
            'storage_name' => $storageName,
            'category' => $category
        ]);

        // Utiliser le dossier temporaire du système (évite les problèmes de permissions)
        $tempPath = sys_get_temp_dir() . '/' . $storageName;

        \Log::info("📁 [EvaluationExportService] Chemin temporaire", [
            'temp_path' => $tempPath
        ]);

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        \Log::info("✅ [EvaluationExportService] Fichier temporaire créé", [
            'temp_path' => $tempPath,
            'size' => filesize($tempPath)
        ]);

        // Lire le contenu du fichier
        $fileContent = file_get_contents($tempPath);
        $fileSize = filesize($tempPath);
        $hashMd5 = md5_file($tempPath);

        // Supprimer le fichier temporaire
        unlink($tempPath);

        // Hasher l'identifiant BIP pour le stockage physique
        $identifiantBip = $project->identifiant_bip;
        $hashedIdentifiantBip = hash('sha256', $identifiantBip);

        // Stocker le fichier selon la structure hashée (même dossier que la fiche)
        $storagePath = "projets/{$hashedIdentifiantBip}/identification";
        $storedPath = "{$storagePath}/{$storageName}";
        Storage::disk('local')->put($storedPath, $fileContent);

        \Log::info("✅ [EvaluationExportService] Fichier stocké", [
            'stored_path' => $storedPath,
            'size' => $fileSize,
            'hash_md5' => $hashMd5
        ]);

        // Générer le hash d'accès
        $hashAcces = $this->generateFileAccessHash($project->hashed_id, $storageName, $category);

        // Vérifier si un export existe déjà pour ce projet
        $existingFile = $project->fichiers()
            ->where('categorie', $category)
            ->where('fichier_attachable_type', IdeeProjet::class)
            ->first();

        if ($existingFile) {
            \Log::info("🔄 [EvaluationExportService] Remplacement de l'ancien fichier", [
                'old_file_id' => $existingFile->id,
                'old_chemin' => $existingFile->chemin
            ]);

            // Supprimer l'ancien fichier physique
            $oldFilePath = storage_path("app/private/{$existingFile->chemin}");
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Supprimer l'entrée de la base de données
            $existingFile->delete();
        }

        \Log::info("📝 [EvaluationExportService] Création de l'entrée en base de données (pertinence)");

        // Créer l'entrée dans la table fichiers (relié à l'IdeeProjet)
        $fichier = $project->fichiers()->create([
            'nom_original' => "evaluation_pertinence_{$identifiantBip}.xlsx",
            'nom_stockage' => $storageName,
            'chemin' => $storedPath,
            'extension' => $extension,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'taille' => $fileSize,
            'hash_md5' => $hashMd5,
            'hash_acces' => $hashAcces,
            'description' => 'Export Excel - Évaluation de pertinence',
            'commentaire' => null,
            'metadata' => [
                'type_document' => 'evaluation-pertinence-export',
                'evaluation_id' => $evaluation->id,
                'idee_projet_id' => $project->id,
                'type_evaluation' => 'pertinence',
                'categorie_originale' => $category,
                'genere_par' => $project->responsableId ?? auth()->id(),
                'genere_le' => now(),
                'dossier_public' => "Projets/{$identifiantBip}/identification"
            ],
            'fichier_attachable_id' => $project->id,
            'fichier_attachable_type' => IdeeProjet::class,
            'categorie' => $category,
            'ordre' => 1,
            'uploaded_by' => $project->responsableId ?? auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);

        \Log::info("✅ [EvaluationExportService] Export pertinence terminé avec succès", [
            'fichier_id' => $fichier->id,
            'stored_path' => $storedPath,
            'project_id' => $project->id,
            'identifiant_bip' => $project->identifiant_bip
        ]);

        return $storedPath;
    }

    /**
     * Remplir les informations du projet dans le template (Feuille 0 - Page de couverture)
     */
    private function fillProjectInfo($sheet, IdeeProjet $project)
    {
        // Feuille 0: Page de couverture
        // Remplir les informations générales du projet

        // B8: Titre du projet
        $sheet->setCellValue('B8', $project->titre_projet ?? '');

        // B10: Numéro BIP
        $sheet->setCellValue('B10', $project->identifiant_bip ?? '');

        // B12: Structure de tutelle
        $sheet->setCellValue('B12', $project->ministere->nom ?? '');

        // B15: Coût du projet
        $cout = is_array($project->cout_estimatif_projet)
            ? ($project->cout_estimatif_projet['montant'] ?? 0)
            : 0;
        $sheet->setCellValue('B15', number_format($cout, 0, ',', ' ') . ' FCFA');

        // B17: Date
        $sheet->setCellValue('B17', now()->format('d/m/Y'));
    }

    /**
     * Remplir les informations du canevas (Feuille 0 - Critères et options de notation)
     * Chaque critère occupe plusieurs lignes:
     * - Ligne N: Intitulé (col A), Description (col B), Labels des notations (col J, K, L, M...)
     * - Ligne N+1: Explications des notations (col J, K, L, M...)
     */
    private function fillCanevasInfo($sheet, $canevas)
    {
        if (empty($canevas) || empty($canevas['criteres'])) {
            return;
        }

        // Ligne de départ pour les critères
        $startRow = 33;
        $colonnesNotations = range('J', 'Z'); // J à Z

        // 🧹 Nettoyer TOUS les critères du template (supposons max 20 critères possibles)
        // Chaque critère occupe 4 lignes
        $maxCriteres = 20;
        for ($i = 0; $i < $maxCriteres; $i++) {
            $baseRow = $startRow + ($i * 4);
            for ($row = $baseRow; $row < $baseRow + 4; $row++) {
                $sheet->setCellValue("A{$row}", '');
                $sheet->setCellValue("B{$row}", '');
                foreach ($colonnesNotations as $col) {
                    $sheet->setCellValue("{$col}{$row}", '');
                }
            }
        }

        // Maintenant remplir les critères
        $currentRow = $startRow;
        foreach ($canevas['criteres'] as $critere) {

            // Colonne A: Intitulé du critère (ligne N)
            if (!empty($critere['intitule'])) {
                $sheet->setCellValue("A{$currentRow}", $critere['intitule']);
            }

            // Colonne B: Description/Commentaire du critère (ligne N)
            if (!empty($critere['commentaire'])) {
                $sheet->setCellValue("B{$currentRow}", $critere['commentaire']);
            }

            // Colonnes J, K, L, M... : Les notations
            //if (!empty($critere['notations'])) {
            if (!empty($critere['notations']) && is_array($critere['notations'])) {
                $colIndex = 0;

                foreach ($critere['notations'] as $notation) {
                    if (!isset($colonnesNotations[$colIndex])) break;

                    if ($colIndex < count($colonnesNotations)) {
                        $column = $colonnesNotations[$colIndex];

                        // Ligne N: Label de la notation "Libellé (Valeur)"
                        $label = ($notation['libelle'] ?? '') . ' (' . ($notation['valeur'] ?? '0') . ')';
                        $sheet->setCellValue("{$column}{$currentRow}", $label);

                        // Ligne N+1: Explication/Commentaire de la notation
                        $commentaire = $notation['commentaire'] ?? '';
                        if (!empty($commentaire)) {
                            $sheet->setCellValue("{$column}" . ($currentRow + 1), $commentaire);
                        }

                        $colIndex++;
                    }
                }

                // ✅ Réinitialiser après chaque critère
                $colIndex = 0;
            }

            // Passer au critère suivant (espacement de 4 lignes entre critères)
            // Ligne N: Intitulé + labels
            // Ligne N+1: Explications
            // Ligne N+2: Vide
            // Ligne N+3: Vide
            // Ligne N+4: Prochain critère
            $currentRow += 4;
        }
    }

    /**
     * Remplir les critères et notations d'évaluation (Feuille 1 - PERTINENCE)
     */
    private function fillPertinenceCriteres($sheet, IdeeProjet $project, $evaluationPertinence)
    {
        // Récupérer le canevas pour l'ordre des critères
        $canevas = $project->canevas_appreciation_pertinence;

        // Les colonnes commencent à C pour le premier critère
        $columns = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];

        // Remplir les en-têtes des critères (ligne 5) et pondérations (ligne 6)
        if (!empty($canevas['criteres'])) {
            $columnIndex = 0;
            foreach ($canevas['criteres'] as $critere) {
                if ($columnIndex < count($columns)) {
                    $column = $columns[$columnIndex];

                    // Ligne 5: Intitulé du critère avec les notations
                    $header = strtoupper($critere['intitule'] ?? '');
                    if (!empty($critere['notations'])) {
                        $annotationLabels = [];
                        foreach ($critere['notations'] as $notation) {
                            $label = $notation['libelle'] ?? '';
                            $valeur = $notation['valeur'] ?? '0';
                            $annotationLabels[] = "{$label}={$valeur}";
                        }
                        $header .= " (" . implode(', ', $annotationLabels) . ")";
                    }
                    if (!empty($critere['commentaire'])) {
                        $header .= "\n" . $critere['commentaire'];
                    }
                    $sheet->setCellValue("{$column}5", $header);

                    // Ligne 6: Pondération (diviser par 100 car les valeurs sont en pourcentage dans le canevas)
                    $ponderation = ((float) $critere['ponderation'] ?? 0) / 100;
                    $sheet->setCellValueExplicit("{$column}6", $ponderation, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                    $columnIndex++;
                }
            }
        }

        // Récupérer les critères évalués depuis l'attribut 'evaluation'
        $evaluationData = $evaluationPertinence->evaluation;

        // Ligne 8: On affiche une seule ligne avec les notes
        $currentRow = 8;

        // A8: Identifiant BIP
        $sheet->setCellValue("A{$currentRow}", $project->identifiant_bip ?? '');

        // B8: Titre du projet
        $sheet->setCellValue("B{$currentRow}", $project->titre_projet ?? '');

        // Créer un mapping des critères par ID pour accès rapide
        $evaluationMap = [];
        foreach ($evaluationData as $evalCritere) {
            $critereId = $evalCritere['critere']['id'] ?? null;
            if ($critereId) {
                $evaluationMap[$critereId] = $evalCritere;
            }
        }

        // Parcourir les critères dans l'ordre du canevas pour remplir les notes
        if (!empty($canevas['criteres'])) {
            $columnIndex = 0;
            foreach ($canevas['criteres'] as $critere) {
                $critereId = $critere['id'] ?? null;

                if ($critereId && isset($evaluationMap[$critereId]) && $columnIndex < count($columns)) {
                    $column = $columns[$columnIndex];
                    $note = $evaluationMap[$critereId]['notation']['valeur'] ?? 0;

                    // Forcer l'affichage même pour 0
                    $sheet->setCellValueExplicit("{$column}{$currentRow}", $note, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                    $columnIndex++;
                }
            }
        }

        // Supprimer les lignes vides en dessous de la ligne du projet (lignes 9, 10, etc.)
        // On ne garde que la ligne 8 avec les données du projet
        $sheet->removeRow(9, $sheet->getHighestRow() - 8);
    }

    /**
     * Remplir les scores agrégés
     * Note: Les scores sont calculés automatiquement par les formules Excel dans la colonne H
     */
    private function fillAggregatedScores($sheet, $evaluationPertinence)
    {
        // Les scores sont calculés automatiquement dans le template Excel
        // via la formule: =SUMPRODUCT(C{row}:G{row},$C$6:$G$6)/SUM($C$6:$G$6)
        // Donc pas besoin de calculer manuellement ici

        // On peut éventuellement ajouter des commentaires ou analyses supplémentaires
    }

    /**
     * Export l'évaluation climatique au format Excel
     */
    public function exportClimatiqueToExcel(Evaluation $evaluation)
    {
        \Log::info("📊 [EvaluationExportService] Début export climatique", [
            'evaluation_id' => $evaluation->id,
            'projetable_type' => $evaluation->projetable_type,
            'projetable_id' => $evaluation->projetable_id
        ]);

        // Charger le template Excel
        $templatePath = base_path('canevas/C-1a_Evaluation_climatique.xlsx');

        \Log::info("📂 [EvaluationExportService] Vérification du template climatique", [
            'template_path' => $templatePath,
            'exists' => file_exists($templatePath)
        ]);

        if (!file_exists($templatePath)) {
            \Log::error("❌ [EvaluationExportService] Template climatique introuvable", [
                'template_path' => $templatePath
            ]);
            throw new \Exception("Template de canevas introuvable: {$templatePath}");
        }

        \Log::info("📄 [EvaluationExportService] Chargement du spreadsheet climatique");

        // Charger le spreadsheet depuis le template
        $spreadsheet = IOFactory::load($templatePath);

        \Log::info("✅ [EvaluationExportService] Spreadsheet climatique chargé");

        // Récupérer le projet
        $project = $evaluation->projetable;

        if (!$project instanceof IdeeProjet) {
            \Log::error("❌ [EvaluationExportService] Type de projet invalide (climatique)", [
                'projetable_type' => get_class($project)
            ]);
            throw new \Exception("L'évaluation doit être liée à une IdeeProjet");
        }

        \Log::info("✅ [EvaluationExportService] Projet climatique récupéré", [
            'project_id' => $project->id,
            'identifiant_bip' => $project->identifiant_bip,
            'titre' => $project->titre_projet
        ]);

        // Récupérer le canevas et l'évaluation depuis le projet
        $canevas = $project->canevas_climatique;
        $evaluationAMC = $project->evaluationAMC->first();

        if (!$evaluationAMC || empty($evaluationAMC->evaluation['climatique'])) {
            throw new \Exception("Aucune évaluation climatique trouvée pour ce projet");
        }

        $evaluationClimatique = $evaluationAMC->evaluation['climatique'];

        // Feuille 0: Page de couverture - Informations du projet et canevas
        $coverSheet = $spreadsheet->getSheet(0);
        $this->fillProjectInfo($coverSheet, $project);
        $this->fillCanevasClimatique($coverSheet, $canevas);

        // Feuille 1: Impact climatique - Résultats de l'évaluation
        $resultSheet = $spreadsheet->getSheet(1);
        $this->fillClimatiqueCriteres($resultSheet, $project, $evaluationClimatique, $canevas);

        // Générer le nom de stockage
        $category = 'evaluation_climatique';
        $extension = 'xlsx';
        $storageName = $this->generateStorageName($category, $evaluation->hashed_id, $extension);

        \Log::info("💾 [EvaluationExportService] Sauvegarde du fichier climatique", [
            'storage_name' => $storageName,
            'category' => $category
        ]);

        // Utiliser le dossier temporaire du système (évite les problèmes de permissions)
        $tempPath = sys_get_temp_dir() . '/' . $storageName;

        \Log::info("📁 [EvaluationExportService] Chemin temporaire climatique", [
            'temp_path' => $tempPath
        ]);

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        \Log::info("✅ [EvaluationExportService] Fichier temporaire climatique créé", [
            'temp_path' => $tempPath,
            'size' => filesize($tempPath)
        ]);

        // Lire le contenu du fichier
        $fileContent = file_get_contents($tempPath);
        $fileSize = filesize($tempPath);
        $hashMd5 = md5_file($tempPath);

        // Supprimer le fichier temporaire
        unlink($tempPath);

        // Hasher l'identifiant BIP pour le stockage physique
        $identifiantBip = $project->identifiant_bip;
        $hashedIdentifiantBip = hash('sha256', $identifiantBip);

        // Stocker le fichier selon la structure hashée (même dossier que la fiche)
        $storagePath = "projets/{$hashedIdentifiantBip}/identification";
        $storedPath = "{$storagePath}/{$storageName}";
        Storage::disk('local')->put($storedPath, $fileContent);

        \Log::info("✅ [EvaluationExportService] Fichier climatique stocké", [
            'stored_path' => $storedPath,
            'size' => $fileSize,
            'hash_md5' => $hashMd5
        ]);

        // Générer le hash d'accès
        $hashAcces = $this->generateFileAccessHash($project->hashed_id, $storageName, $category);

        // Vérifier si un export existe déjà pour ce projet
        $existingFile = $project->fichiers()
            ->where('categorie', $category)
            ->where('fichier_attachable_type', IdeeProjet::class)
            ->first();

        if ($existingFile) {
            \Log::info("🔄 [EvaluationExportService] Remplacement de l'ancien fichier climatique", [
                'old_file_id' => $existingFile->id,
                'old_chemin' => $existingFile->chemin
            ]);

            // Supprimer l'ancien fichier physique
            $oldFilePath = storage_path("app/private/{$existingFile->chemin}");
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Supprimer l'entrée de la base de données
            $existingFile->delete();
        }

        \Log::info("📝 [EvaluationExportService] Création de l'entrée en base de données (climatique)");

        // Créer l'entrée dans la table fichiers (relié à l'IdeeProjet)
        $fichier = $project->fichiers()->create([
            'nom_original' => "evaluation_climatique_{$identifiantBip}.xlsx",
            'nom_stockage' => $storageName,
            'chemin' => $storedPath,
            'extension' => $extension,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'taille' => $fileSize,
            'hash_md5' => $hashMd5,
            'hash_acces' => $hashAcces,
            'description' => 'Export Excel - Évaluation climatique',
            'commentaire' => null,
            'metadata' => [
                'type_document' => 'evaluation-climatique-export',
                'evaluation_id' => $evaluation->id,
                'idee_projet_id' => $project->id,
                'type_evaluation' => 'climatique',
                'categorie_originale' => $category,
                'genere_par' => $project->responsableId ?? auth()->id(),
                'genere_le' => now(),
                'dossier_public' => "Projets/{$identifiantBip}/identification"
            ],
            'fichier_attachable_id' => $project->id,
            'fichier_attachable_type' => IdeeProjet::class,
            'categorie' => $category,
            'ordre' => 1,
            'uploaded_by' => $project->responsableId ?? auth()->id(),
            'is_public' => false,
            'is_active' => true
        ]);

        \Log::info("✅ [EvaluationExportService] Export climatique terminé avec succès", [
            'fichier_id' => $fichier->id,
            'stored_path' => $storedPath,
            'project_id' => $project->id,
            'identifiant_bip' => $project->identifiant_bip
        ]);

        return $storedPath;
    }

    /**
     * Remplir les informations du canevas climatique (Feuille 0 - Critères et annotations)
     * Chaque critère occupe plusieurs lignes:
     * - Ligne N: Intitulé (col A), Description (col B), Labels des notations (col J, K, L, M...)
     * - Ligne N+1: Explications des notations (col J, K, L, M...)
     */
    private function fillCanevasClimatique($sheet, $canevas)
    {
        if (empty($canevas) || empty($canevas['criteres'])) {
            return;
        }

        // Ligne de départ pour les critères
        $startRow = 32;
        $colonnesNotations = range('J', 'Z'); // J à Z

        // 🧹 Nettoyer TOUS les critères du template (supposons max 20 critères possibles)
        // Chaque critère occupe 4 lignes
        $maxCriteres = 20;
        for ($i = 0; $i < $maxCriteres; $i++) {
            $baseRow = $startRow + ($i * 4);
            for ($row = $baseRow; $row < $baseRow + 4; $row++) {
                $sheet->setCellValue("A{$row}", '');
                $sheet->setCellValue("B{$row}", '');
                foreach ($colonnesNotations as $col) {
                    $sheet->setCellValue("{$col}{$row}", '');
                }
            }
        }

        // Maintenant remplir les critères
        $currentRow = $startRow;
        foreach ($canevas['criteres'] as $critere) {

            // Colonne A: Intitulé du critère (ligne N)
            if (!empty($critere['intitule'])) {
                $sheet->setCellValue("A{$currentRow}", $critere['intitule']);
            }

            // Colonne B: Description/Commentaire du critère (ligne N)
            if (!empty($critere['commentaire'])) {
                $sheet->setCellValue("B{$currentRow}", $critere['commentaire']);
            }

            // Colonnes J, K, L, M... : Les notations
            if (!empty($critere['notations']) && is_array($critere['notations'])) {
                $colIndex = 0;

                foreach ($critere['notations'] as $notation) {
                    if ($colIndex < count($colonnesNotations)) {
                        $column = $colonnesNotations[$colIndex];

                        // Ligne N: Label de la notation "Libellé (Valeur)"
                        $label = ($notation['libelle'] ?? '') . ' (' . ($notation['valeur'] ?? '0') . ')';
                        $sheet->setCellValue("{$column}{$currentRow}", $label);

                        // Ligne N+1: Explication/Commentaire de la notation
                        $commentaire = $notation['commentaire'] ?? '';
                        if (!empty($commentaire)) {
                            $sheet->setCellValue("{$column}" . ($currentRow + 1), $commentaire);
                        }

                        $colIndex++;
                    }
                }
            }

            // Passer au critère suivant (espacement de 4 ou 5 lignes selon le template)
            // Ligne N: Intitulé + labels
            // Ligne N+1: Explications (peut s'étendre sur plusieurs lignes fusionnées)
            // Espacement jusqu'au prochain critère
            $currentRow += 4;
        }
    }

    /**
     * Remplir les critères et notations d'évaluation climatique (Feuille 1)
     */
    private function fillClimatiqueCriteres($sheet, IdeeProjet $project, array $evaluationClimatique, $canevas)
    {
        // Les colonnes commencent à C pour le premier critère
        $columns = ['C', 'D', 'E', 'F'];

        // Remplir les en-têtes des critères (ligne 5) et pondérations (ligne 6)
        if (!empty($canevas['criteres'])) {
            $columnIndex = 0;
            foreach ($canevas['criteres'] as $critere) {
                if ($columnIndex < count($columns)) {
                    $column = $columns[$columnIndex];

                    // Ligne 5: Intitulé du critère avec les notations
                    $header = $critere['intitule'] ?? '';
                    if (!empty($critere['notations'])) {
                        $annotationLabels = [];
                        foreach ($critere['notations'] as $notation) {
                            $label = $notation['libelle'] ?? '';
                            $valeur = $notation['valeur'] ?? '0';
                            $annotationLabels[] = "{$label}={$valeur}";
                        }
                        $header .= " (" . implode(', ', $annotationLabels) . ")";
                    }
                    $sheet->setCellValue("{$column}5", $header);

                    // Ligne 6: Pondération (diviser par 100 car la cellule est formatée en pourcentage)
                    $ponderation = ((float) $critere['ponderation'] ?? 0) / 100;
                    $sheet->setCellValueExplicit("{$column}6", $ponderation, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                    $columnIndex++;
                }
            }
        }

        // Ligne 8: On affiche une seule ligne avec les notes
        $currentRow = 8;

        // A8: Numéro (peut être laissé vide ou incrémenté)
        $sheet->setCellValue("A{$currentRow}", 1);

        // B8: Titre du projet
        $sheet->setCellValue("B{$currentRow}", $project->titre_projet ?? '');

        // Créer un mapping des critères par ID (hashed) pour accès rapide
        $evaluationMap = [];
        if (!empty($evaluationClimatique['evaluation_effectuer'])) {
            foreach ($evaluationClimatique['evaluation_effectuer'] as $evalCritere) {
                $critereHashedId = $evalCritere['critere']['id'] ?? null;
                if ($critereHashedId) {
                    $evaluationMap[$critereHashedId] = $evalCritere;
                }
            }
        }

        // Parcourir les critères dans l'ordre du canevas pour remplir les notes
        if (!empty($canevas['criteres'])) {
            $columnIndex = 0;
            foreach ($canevas['criteres'] as $critere) {
                $critereId = $critere['id'] ?? null;

                if ($critereId && isset($evaluationMap[$critereId]) && $columnIndex < count($columns)) {
                    $column = $columns[$columnIndex];
                    $note = $evaluationMap[$critereId]['notation']['valeur'] ?? 0;

                    // Forcer l'affichage même pour 0 ou les valeurs négatives
                    $sheet->setCellValueExplicit("{$column}{$currentRow}", $note, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                    $columnIndex++;
                }
            }
        }

        // Supprimer les lignes vides en dessous de la ligne du projet (lignes 9, 10, etc.)
        // On ne garde que la ligne 8 avec les données du projet
        $sheet->removeRow(9, $sheet->getHighestRow() - 8);
    }

    /**
     * Générer un nom de stockage selon la catégorie
     */
    private function generateStorageName(string $category, string $evaluationId, string $extension): string
    {
        $prefix = match ($category) {
            'evaluation_pertinence' => 'eval_pertinence',
            'evaluation_climatique' => 'eval_climatique',
            'evaluation_amc' => 'eval_amc',
            default => $category
        };

        // Remplacer les slashes pour éviter de créer des sous-dossiers non désirés
        $sanitizedId = str_replace('/', '_', $evaluationId);

        return $prefix . '_' . $sanitizedId . '_' . time() . '.' . $extension;
    }

    /**
     * Générer le hash d'accès public pour un fichier
     */
    private function generateFileAccessHash(string $evaluationId, string $storageName, string $category): string
    {
        return hash('sha256', $evaluationId . $storageName . $category . config('app.key'));
    }
}
