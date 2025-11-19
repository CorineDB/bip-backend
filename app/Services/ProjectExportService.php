<?php

namespace App\Services;

use App\Models\IdeeProjet;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\ComplexType\TblWidth;
use Illuminate\Support\Facades\Storage;

class ProjectExportService
{
    /**
     * Export en PDF avec table des matières
     *
     * @param IdeeProjet $project
     * @param bool $returnResponse Si true, retourne une Response de téléchargement. Si false, retourne un array avec les infos du fichier.
     * @return \Illuminate\Http\Response|array
     */
    public function exportToPdf(IdeeProjet $project, bool $returnResponse = true)
    {
        $data = [
            'project' => $project,
            'logo_url' => public_path('images/benin-logo.png'),
            'toc' => $this->generateTableOfContents($project),
            'fondementData' => $this->construireFondement($project)
        ];

        $pdf = Pdf::loadView('exports.project-idea-with-toc', $data);
        $pdf->setPaper('A4', 'portrait');

        // Options pour numérotation des pages
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        // Générer le nom de stockage
        $category = 'fiche_idee_projet';
        $extension = 'pdf';
        $storageName = $this->generateStorageName($category, $project->identifiant_bip, $extension);

        // Hasher l'identifiant BIP pour le stockage physique
        $identifiantBip = $project->identifiant_bip;
        $hashedIdentifiantBip = hash('sha256', $identifiantBip);
        $hashedProjectId = hash('sha256', $project->id);

        // Stocker le fichier selon la nouvelle structure (chemin hashé)
        $storagePath = "projets/{$hashedIdentifiantBip}/identification";

        // Sauvegarder le PDF temporairement pour obtenir le contenu
        $pdfContent = $pdf->output();

        // Calculer taille et hash MD5 du contenu
        $fileSize = strlen($pdfContent);
        $hashMd5 = md5($pdfContent);

        // Utiliser Storage::disk('local')->put pour stocker
        $storedPath = "{$storagePath}/{$storageName}";
        $success = Storage::disk('local')->put($storedPath, $pdfContent);

        // Vérifier que le fichier a bien été créé
        if (!$success) {
            throw new \Exception("Impossible de sauvegarder le fichier PDF à {$storedPath}");
        }

        // Générer le hash d'accès
        $hashAcces = $this->generateFileAccessHash($project->hashed_id, $storageName, $category);

        // Vérifier si une fiche existe déjà pour ce projet
        $existingFiche = $project->fichiers()
            ->where('categorie', $category)
            ->where('fichier_attachable_type', IdeeProjet::class)
            ->first();

        if ($existingFiche) {
            // Supprimer l'ancien fichier physique
            $oldFilePath = storage_path("app/{$existingFiche->chemin}");
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Supprimer l'entrée de la base de données
            $existingFiche->delete();
        }

        // Créer l'entrée dans la table fichiers
        $fichier = $project->fichiers()->create([
            'nom_original' => "fiche_idee_projet_{$identifiantBip}.pdf",
            'nom_stockage' => $storageName,
            'chemin' => $storedPath,
            'extension' => $extension,
            'mime_type' => 'application/pdf',
            'taille' => $fileSize,
            'hash_md5' => $hashMd5,
            'hash_acces' => $hashAcces,
            'description' => 'Fiche d\'idée de projet (export PDF)',
            'commentaire' => null,
            'metadata' => [
                'type_document' => 'fiche-idee-projet',
                'idee_projet_id' => $project->id,
                'projet_id' => $project->id,
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

        // Si appelé depuis un job, retourner un array avec les infos
        if (!$returnResponse) {
            return [
                'success' => true,
                'fichier_id' => $fichier->id,
                'storage_path' => $storedPath,
                'file_name' => $storageName,
                'size' => $fileSize,
                'size_formatted' => $this->formatBytes($fileSize),
                'md5' => $hashMd5,
            ];
        }

        // Sinon, retourner la Response de téléchargement
        return $pdf->download("fiche_projet_{$project->identifiant_bip}.pdf");
    }

    /**
     * Formater les bytes en taille lisible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Générer un nom de stockage selon la catégorie
     */
    private function generateStorageName(string $category, string $projectId, string $extension): string
    {
        $prefix = match ($category) {
            'fiche_idee_projet' => 'fiche_idee_projet',
            default => $category
        };

        // Remplacer les slashes pour éviter de créer des sous-dossiers non désirés
        $sanitizedId = str_replace('/', '_', $projectId);

        return $prefix . '_' . $sanitizedId . '_' . time() . '.' . $extension;
    }

    /**
     * Générer le hash d'accès public pour un fichier
     */
    private function generateFileAccessHash(string $projectId, string $storageName, string $category): string
    {
        return hash('sha256', $projectId . $storageName . $category . config('app.key'));
    }

    /**
     * Export en Word avec table des matières automatique
     */
    public function exportToWord(IdeeProjet $project)
    {
        $phpWord = new PhpWord();

        // Configuration du document
        $phpWord->getSettings()->setUpdateFields(true);
        $phpWord->getSettings()->setAutoHyphenation(true);

        // Définir les styles de titres pour la table des matières
        $this->defineStyles($phpWord);

        // Page de garde
        $coverSection = $phpWord->addSection();
        $this->addCoverPage($coverSection, $project);

        // Table des matières (manually created due to PhpWord version limitations)
        $tocSection = $phpWord->addSection(['breakType' => 'nextPage']);
        $this->addManualTableOfContents($tocSection);

        // Contenu principal avec numérotation
        $mainSection = $phpWord->addSection([
            'breakType' => 'nextPage',
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginLeft' => 1440,
            'marginRight' => 1440,
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'headerHeight' => 720,
            'footerHeight' => 720
        ]);

        // Ajouter en-tête et pied de page avec numéros
        $this->addHeaderFooter($mainSection, $project);

        // Sections du document avec styles de titre
        $this->addSection1_OrigineProjet($mainSection, $project);
        $this->addSection2_DescriptionSommaire($mainSection, $project);
        $this->addSection3_Evaluation($mainSection, $project);

        // Générer le fichier
        $filename = "fiche_projet_{$project->identifiant_bip}.docx";
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $temp = storage_path("app/temp/{$filename}");
        $writer->save($temp);

        return response()->download($temp)->deleteFileAfterSend();
    }

    /**
     * Définir les styles pour la table des matières
     */
    private function defineStyles($phpWord)
    {
        // Style Titre 1
        $phpWord->addTitleStyle(1,
            ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '2E74B5'],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 240]
        );

        // Style Titre 2
        $phpWord->addTitleStyle(2,
            ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '2E74B5'],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 200]
        );

        // Style Titre 3
        $phpWord->addTitleStyle(3,
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 160]
        );

        // Style Table des matières
        $phpWord->addTitleStyle('TOC',
            ['name' => 'Arial', 'size' => 18, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
        );

        // Style pour les entrées de la table des matières
        // Note: addStyle with array config may cause issues in some PhpWord versions
        // $tocStyle = ['tabLeader' => \PhpOffice\PhpWord\Style\TOC::TAB_LEADER_DOT, 'indentation' => ['left' => 200]];
        // $phpWord->addStyle('TOC_Style', $tocStyle);
    }

    /**
     * Page de garde
     */
    private function addCoverPage($section, $project)
    {
        // Logo (temporarily disabled - requires PHP GD extension)
        // $section->addImage(public_path('images/benin-logo.png'), [
        //     'width' => 150,
        //     'height' => 150,
        //     'alignment' => Jc::CENTER,
        //     'wrappingStyle' => 'inline'
        // ]);

        $section->addTextBreak(2);

        // Titre République
        $section->addText(
            'RÉPUBLIQUE DU BÉNIN',
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );

        $section->addText(
            'MINISTÈRE DU DÉVELOPPEMENT ET DE LA COORDINATION',
            ['name' => 'Arial', 'size' => 12],
            ['alignment' => Jc::CENTER]
        );

        $section->addText(
            'DE L\'ACTION GOUVERNEMENTALE',
            ['name' => 'Arial', 'size' => 12],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(5);

        // Titre principal
        $section->addText(
            'FICHE D\'IDÉE DE PROJET',
            ['name' => 'Arial', 'size' => 24, 'bold' => true, 'color' => '00A651'],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(3);

        // Informations du projet
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 100,
            'alignment' => Jc::CENTER,
            'width' => 9000
        ]);

        $table->addRow(400);
        $cell = $table->addCell(9000, ['bgColor' => 'F2F2F2']);
        $cell->addText('TITRE DU PROJET', ['bold' => true, 'size' => 12]);

        $table->addRow(600);
        $cell = $table->addCell(9000);
        $cell->addText($project->titre_projet ?? '', ['size' => 14, 'bold' => true], ['alignment' => Jc::CENTER]);

        $section->addTextBreak(3);

        // Numéro BIP et autres infos
        $infoTable = $section->addTable(['alignment' => Jc::CENTER]);
        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Numéro BIP:', ['bold' => true]);
        $infoTable->addCell(4500)->addText($project->identifiant_bip ?: 'Non attribué');

        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Structure de tutelle:', ['bold' => true]);
        $infoTable->addCell(4500)->addText($project->ministere->nom ?? 'Non renseigné');

        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Coût estimé:', ['bold' => true]);
        $cout = is_array($project->cout_estimatif_projet) ? ($project->cout_estimatif_projet['montant'] ?? 0) : 0;
        $infoTable->addCell(4500)->addText(number_format($cout, 0, ',', ' ') . ' FCFA');

        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Date d\'élaboration:', ['bold' => true]);
        $infoTable->addCell(4500)->addText(now()->format('d/m/Y'));
    }

    /**
     * Ajouter une table des matières manuelle
     */
    private function addManualTableOfContents($section)
    {
        // Titre
        $section->addText(
            'TABLE DES MATIÈRES',
            ['name' => 'Arial', 'size' => 18, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
        );

        // Contenu manuel de la table des matières
        $toc = [
            ['title' => 'I. Origine du projet', 'level' => 1],
            ['title' => '1. Titre du projet', 'level' => 2],
            ['title' => '2. Origine du projet', 'level' => 2],
            ['title' => '3. Fondement', 'level' => 2],
            ['title' => '4. Situation actuelle', 'level' => 2],
            ['title' => '5. Situation désirée', 'level' => 2],
            ['title' => '6. Contraintes à respecter et gérer', 'level' => 2],
            ['title' => 'II. Description sommaire de l\'idée de projet', 'level' => 1],
            ['title' => '1. Description générale du projet', 'level' => 2],
            ['title' => '2. Échéancier des principaux extrants', 'level' => 2],
            ['title' => '3. Description des principaux extrants', 'level' => 2],
            ['title' => '4. Caractéristiques techniques du projet', 'level' => 2],
            ['title' => '5. Localisation, choix du ou des site(s) d\'accueil', 'level' => 2],
            ['title' => '6. Aspects organisationnels du projet', 'level' => 2],
            ['title' => 'III. Évaluation et recommandations', 'level' => 1],
            ['title' => '7. Estimation des coûts et des bénéfices', 'level' => 2],
            ['title' => '8. Risques immédiats', 'level' => 2],
            ['title' => '9. Conclusions et recommandations', 'level' => 2],
        ];

        foreach ($toc as $item) {
            $indent = $item['level'] == 1 ? 0 : 360;
            $bold = $item['level'] == 1;
            $section->addText(
                $item['title'],
                ['name' => 'Arial', 'size' => 11, 'bold' => $bold],
                ['indentation' => ['left' => $indent], 'spaceAfter' => 120]
            );
        }

        $section->addTextBreak(2);
    }

    /**
     * En-tête et pied de page avec numérotation
     */
    private function addHeaderFooter($section, $project)
    {
        // En-tête
        $header = $section->addHeader();
        $headerTable = $header->addTable(['width' => 9000]);
        $headerTable->addRow();
        $headerTable->addCell(4500)->addText('Fiche d\'Idée de Projet', ['size' => 10]);
        $headerTable->addCell(4500, ['alignment' => Jc::RIGHT])
            ->addText($project->identifiant_bip ?: 'Document de travail', ['size' => 10]);

        // Pied de page avec numéro de page
        $footer = $section->addFooter();
        $footer->addPreserveText(
            'Page {PAGE} sur {NUMPAGES}',
            ['size' => 10],
            ['alignment' => Jc::CENTER]
        );
    }

    /**
     * Construire le fondement selon ExternalApiService
     */
    private function construireFondement($project): array
    {
        $odds = $project->odds->map(function ($odd) {
            return $odd->odd ?? $odd->nom;
        })->filter()->values()->toArray();

        $cibles = $project->cibles->map(function ($cible) {
            return $cible->cible ?? $cible->nom;
        })->filter()->values()->toArray();

        $orientations_strategique_pnd = $project->orientations_strategique_pnd->map(function ($orientation_strategique_png) {
            return $orientation_strategique_png->intitule;
        })->filter()->values()->toArray();

        $objectifs_strategique_pnd = $project->objectifs_strategique_pnd->map(function ($objectif_strategique_png) {
            return $objectif_strategique_png->intitule;
        })->filter()->values()->toArray();

        $piliers_pag = $project->piliers_pag->map(function ($pilier_pag) {
            return $pilier_pag->intitule;
        })->filter()->values()->toArray();

        $axes_pag = $project->axes_pag->map(function ($axe_pag) {
            return $axe_pag->intitule;
        })->filter()->values()->toArray();

        $actions_pag = $project->actions_pag->map(function ($action_pag) {
            return $action_pag->intitule;
        })->filter()->values()->toArray();

        return [
            'odds' => !empty($odds) ? $odds : [],
            'cibles' => !empty($cibles) ? $cibles : [],
            'informationsPND' => [
                'orientationsStrategiques' => !empty($orientations_strategique_pnd) ? $orientations_strategique_pnd : [],
                'objectifsStrategiques' => !empty($objectifs_strategique_pnd) ? $objectifs_strategique_pnd : [],
            ],
            'informationsPAG' => [
                'piliersStrategiques' => !empty($piliers_pag) ? $piliers_pag : [],
                'axes' => !empty($axes_pag) ? $axes_pag : [],
                'actions' => !empty($actions_pag) ? $actions_pag : [],
            ],
        ];
    }

    /**
     * Section 1: Origine du projet
     */
    private function addSection1_OrigineProjet($section, $project)
    {
        // Titre niveau 1
        $section->addTitle('Origine du projet', 1);

        // 1.1 Titre du projet
        $section->addTitle('1. Titre du projet', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 60,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->titre_projet ?? '');

        // 1.2 Origine du projet
        $section->addTitle('2. Origine du projet', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->origine ?? '');

        // 1.3 Fondement
        $section->addTitle('3. Fondement', 2);
        $section->addText('(Action de la stratégie/Plan/Programme)', ['italic' => true, 'size' => 10]);

        // Construire fondement selon ExternalApiService
        $fondementData = $this->construireFondement($project);

        // Créer un tableau pour simuler un cadrant
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '999999']);
        $table->addRow();
        $cell = $table->addCell(9000, ['valign' => 'top']);

        // ODD (Objectifs de Développement Durable)
        if (!empty($fondementData['odds'])) {
            $cell->addText('ODD (Objectifs de Développement Durable):', ['bold' => true, 'color' => '2E74B5', 'size' => 10]);
            foreach ($fondementData['odds'] as $odd) {
                $cell->addText('  • ' . $odd, ['size' => 9]);
            }
            $cell->addTextBreak();
        }

        // Cibles
        if (!empty($fondementData['cibles'])) {
            $cell->addText('Cibles:', ['bold' => true, 'color' => '2E74B5', 'size' => 10]);
            foreach ($fondementData['cibles'] as $cible) {
                $cell->addText('  • ' . $cible, ['size' => 9]);
            }
            $cell->addTextBreak();
        }

        // Informations PND (Plan National de Développement)
        if (!empty($fondementData['informationsPND']['orientationsStrategiques']) ||
            !empty($fondementData['informationsPND']['objectifsStrategiques'])) {
            $cell->addText('Informations PND (Plan National de Développement):', ['bold' => true, 'color' => '2E74B5', 'size' => 10]);

            if (!empty($fondementData['informationsPND']['orientationsStrategiques'])) {
                $cell->addText('  Orientations stratégiques:', ['italic' => true, 'size' => 9]);
                foreach ($fondementData['informationsPND']['orientationsStrategiques'] as $orientation) {
                    $cell->addText('    • ' . $orientation, ['size' => 9]);
                }
            }

            if (!empty($fondementData['informationsPND']['objectifsStrategiques'])) {
                $cell->addText('  Objectifs stratégiques:', ['italic' => true, 'size' => 9]);
                foreach ($fondementData['informationsPND']['objectifsStrategiques'] as $objectif) {
                    $cell->addText('    • ' . $objectif, ['size' => 9]);
                }
            }
            $cell->addTextBreak();
        }

        // Informations PAG (Plan d'Action Gouvernemental)
        if (!empty($fondementData['informationsPAG']['piliersStrategiques']) ||
            !empty($fondementData['informationsPAG']['axes']) ||
            !empty($fondementData['informationsPAG']['actions'])) {
            $cell->addText('Informations PAG (Plan d\'Action Gouvernemental):', ['bold' => true, 'color' => '2E74B5', 'size' => 10]);

            if (!empty($fondementData['informationsPAG']['piliersStrategiques'])) {
                $cell->addText('  Piliers stratégiques:', ['italic' => true, 'size' => 9]);
                foreach ($fondementData['informationsPAG']['piliersStrategiques'] as $pilier) {
                    $cell->addText('    • ' . $pilier, ['size' => 9]);
                }
            }

            if (!empty($fondementData['informationsPAG']['axes'])) {
                $cell->addText('  Axes:', ['italic' => true, 'size' => 9]);
                foreach ($fondementData['informationsPAG']['axes'] as $axe) {
                    $cell->addText('    • ' . $axe, ['size' => 9]);
                }
            }

            if (!empty($fondementData['informationsPAG']['actions'])) {
                $cell->addText('  Actions:', ['italic' => true, 'size' => 9]);
                foreach ($fondementData['informationsPAG']['actions'] as $action) {
                    $cell->addText('    • ' . $action, ['size' => 9]);
                }
            }
        }

        $section->addTextBreak();

        // 1.4 Situation actuelle
        $section->addTitle('4. Situation actuelle', 2);
        $section->addText('(Problématique et/ou besoins)', ['italic' => true, 'size' => 10]);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->situation_actuelle ?? '');

        // 1.5 Situation désirée
        $section->addTitle('5. Situation désirée', 2);
        $section->addText('(Finalité, Buts)', ['italic' => true, 'size' => 10]);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->situation_desiree ?? '');

        // 1.6 Contraintes
        $section->addTitle('6. Contraintes à respecter et gérer', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->contraintes ?? '');

        $section->addPageBreak();
    }

    /**
     * Section 2: Description sommaire
     */
    private function addSection2_DescriptionSommaire($section, $project)
    {
        $section->addTitle('Description sommaire de l\'idée de projet', 1);

        // 2.1 Description générale
        $section->addTitle('1. Description générale du projet', 2);
        $section->addText('(Contexte & objectifs)', ['italic' => true, 'size' => 10]);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->description_projet ?? $project->description ?? '');

        // 2.2 Échéancier
        $section->addTitle('2. Échéancier des principaux extrants', 2);
        $section->addText('(Indicateurs de réalisations physiques)', ['italic' => true, 'size' => 10]);

        if ($project->echeancier) {
            $echeancierData = is_string($project->echeancier) ? json_decode($project->echeancier, true) : $project->echeancier;
            if ($echeancierData && is_array($echeancierData)) {
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
                $table->addRow();
                $table->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Extrant', ['bold' => true]);
                $table->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Date prévue', ['bold' => true]);
                $table->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Indicateur', ['bold' => true]);

                foreach ($echeancierData as $output) {
                    $table->addRow();
                    $table->addCell(3000)->addText($output['name'] ?? $output['extrant'] ?? '');
                    $table->addCell(3000)->addText($output['date'] ?? $output['date_prevue'] ?? '');
                    $table->addCell(3000)->addText($output['indicator'] ?? $output['indicateur'] ?? '');
                }
            }
        }

        // 2.3 Description des extrants
        $section->addTitle('3. Description des principaux extrants', 2);
        $section->addText('(spécifications techniques)', ['italic' => true, 'size' => 10]);

        if ($project->description_extrants) {
            $extrantsData = is_string($project->description_extrants) ? json_decode($project->description_extrants, true) : $project->description_extrants;
            if ($extrantsData && is_array($extrantsData)) {
                foreach ($extrantsData as $i => $spec) {
                    if (is_array($spec)) {
                        $section->addTitle('3.' . ($i + 1) . ' ' . ($spec['title'] ?? $spec['titre'] ?? 'Extrant ' . ($i + 1)), 3);
                        $section->addText($spec['description'] ?? '');
                    }
                }
            }
        }

        // 2.4 Caractéristiques techniques
        $section->addTitle('4. Caractéristiques techniques du projet', 2);
        $this->addWarningBox($section);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $caracteristiques = is_array($project->caracteristiques) ? json_encode($project->caracteristiques, JSON_UNESCAPED_UNICODE) : ($project->caracteristiques ?? '');
        $textBox->addText($caracteristiques);

        // 2.5 Localisation
        $section->addTitle('5. Localisation, choix du ou des site(s) d\'accueil et impact environnemental probable', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->impact_environnement ?? '');

        // 2.6 Aspects organisationnels
        $section->addTitle('6. Aspects organisationnels du projet', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->aspect_organisationnel ?? '');

        $section->addPageBreak();
    }

    /**
     * Section 3: Évaluation
     */
    private function addSection3_Evaluation($section, $project)
    {
        $section->addTitle('Évaluation et recommandations', 1);

        // 3.1 Estimation des coûts et bénéfices
        $section->addTitle('7. Estimation des coûts et des bénéfices', 2);

        if ($project->estimation_couts) {
            $costBenefits = is_string($project->estimation_couts) ? json_decode($project->estimation_couts, true) : $project->estimation_couts;

            if ($costBenefits && is_array($costBenefits)) {
                // Tableau des coûts
                $section->addText('Estimation des coûts:', ['bold' => true]);
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
                $table->addRow();
                $table->addCell(4500, ['bgColor' => 'F2F2F2'])->addText('Poste de dépense', ['bold' => true]);
                $table->addCell(2000, ['bgColor' => 'F2F2F2'])->addText('Montant (FCFA)', ['bold' => true]);
                $table->addCell(2500, ['bgColor' => 'F2F2F2'])->addText('Pourcentage', ['bold' => true]);

                $totalCost = 0;
                foreach ($costBenefits['costs'] ?? $costBenefits['couts'] ?? [] as $cost) {
                    $table->addRow();
                    $table->addCell(4500)->addText($cost['item'] ?? $cost['poste'] ?? '');
                    $montant = $cost['amount'] ?? $cost['montant'] ?? 0;
                    $table->addCell(2000)->addText(number_format($montant, 0, ',', ' '));
                    $table->addCell(2500)->addText(($cost['percentage'] ?? $cost['pourcentage'] ?? 0) . '%');
                    $totalCost += $montant;
                }

                $table->addRow();
                $table->addCell(4500, ['bgColor' => 'E8E8E8'])->addText('TOTAL', ['bold' => true]);
                $table->addCell(2000, ['bgColor' => 'E8E8E8'])->addText(number_format($totalCost, 0, ',', ' '), ['bold' => true]);
                $table->addCell(2500, ['bgColor' => 'E8E8E8'])->addText('100%', ['bold' => true]);

                $section->addTextBreak();

                // Bénéfices attendus
                $section->addText('Bénéfices attendus:', ['bold' => true]);
                foreach ($costBenefits['benefits'] ?? $costBenefits['benefices'] ?? [] as $benefit) {
                    $section->addText('• ' . $benefit, ['size' => 11], ['indentation' => ['left' => 360]]);
                }
            }
        }

        // 3.2 Risques immédiats
        $section->addTitle('8. Risques immédiats', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $risques = is_array($project->risques_immediats) ? json_encode($project->risques_immediats, JSON_UNESCAPED_UNICODE) : ($project->risques_immediats ?? '');
        $textBox->addText($risques);

        // 3.3 Conclusions et recommandations
        $section->addTitle('9. Conclusions et recommandations', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->conclusions ?? '');

        // Solutions alternatives (champ non existant dans IdeeProjet, on laisse vide)
        $section->addTitle('Autres solutions alternatives considérées et non retenues', 1);
        $section->addText('Aucune solution alternative documentée.', ['italic' => true]);

        // Signatures
        $section->addTextBreak(3);
        $signatureTable = $section->addTable();
        $signatureTable->addRow();
        $cell1 = $signatureTable->addCell(4500);
        $cell1->addText('Demandeur', ['bold' => true]);
        $cell1->addTextBreak(3);
        $cell1->addText('_______________________');
        $cell1->addText('Date et signature');

        $cell2 = $signatureTable->addCell(4500);
        $cell2->addText('Responsable', ['bold' => true]);
        $cell2->addTextBreak(3);
        $cell2->addText('_______________________');
        $cell2->addText('Date et signature');
    }

    /**
     * Ajouter une boîte d'avertissement
     */
    private function addWarningBox($section)
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '007BFF',
            'cellMargin' => 100,
            'width' => 9000
        ]);

        $table->addRow();
        $cell = $table->addCell(9000, ['bgColor' => 'E8F4FD']);
        $cell->addText('Erreurs fréquentes à éviter', ['bold' => true, 'color' => '007BFF']);
        $cell->addText(
            'La description des extrants du projet exige de sortir de la tendance à citer ses interventions ou activités. ' .
            'Les variables économiques (revenu par habitant, emplois générés, consommation par habitant, etc.) doivent être mesurables.',
            ['size' => 10]
        );

        $section->addTextBreak();
    }

    /**
     * Générer la structure de la table des matières pour PDF
     */
    private function generateTableOfContents($project)
    {
        return [
            [
                'title' => 'Origine du projet',
                'page' => 2,
                'level' => 1,
                'children' => [
                    ['title' => '1. Titre du projet', 'page' => 2, 'level' => 2],
                    ['title' => '2. Origine du projet', 'page' => 2, 'level' => 2],
                    ['title' => '3. Fondement', 'page' => 2, 'level' => 2],
                    ['title' => '4. Situation actuelle', 'page' => 2, 'level' => 2],
                    ['title' => '5. Situation désirée', 'page' => 2, 'level' => 2],
                    ['title' => '6. Contraintes à respecter et gérer', 'page' => 3, 'level' => 2]
                ]
            ],
            [
                'title' => 'Description sommaire de l\'idée de projet',
                'page' => 4,
                'level' => 1,
                'children' => [
                    ['title' => '1. Description générale du projet', 'page' => 4, 'level' => 2],
                    ['title' => '2. Échéancier des principaux extrants', 'page' => 4, 'level' => 2],
                    ['title' => '3. Description des principaux extrants', 'page' => 4, 'level' => 2],
                    ['title' => '4. Caractéristiques techniques du projet', 'page' => 5, 'level' => 2],
                    ['title' => '5. Localisation et impact environnemental', 'page' => 5, 'level' => 2],
                    ['title' => '6. Aspects organisationnels du projet', 'page' => 5, 'level' => 2]
                ]
            ],
            [
                'title' => 'Évaluation et recommandations',
                'page' => 6,
                'level' => 1,
                'children' => [
                    ['title' => '7. Estimation des coûts et des bénéfices', 'page' => 6, 'level' => 2],
                    ['title' => '8. Risques immédiats', 'page' => 6, 'level' => 2],
                    ['title' => '9. Conclusions et recommandations', 'page' => 6, 'level' => 2]
                ]
            ],
            [
                'title' => 'Autres solutions alternatives considérées',
                'page' => 6,
                'level' => 1,
                'children' => []
            ]
        ];
    }
}
