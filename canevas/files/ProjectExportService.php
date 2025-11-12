<?php

namespace App\Services;

use App\Models\IdeeProjet;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\ComplexType\TblWidth;

class ProjectExportService
{
    /**
     * Export en PDF avec table des matières
     */
    public function exportToPdf(IdeeProjet $project)
    {
        $data = [
            'project' => $project,
            'logo_url' => public_path('images/benin-logo.png'),
            'toc' => $this->generateTableOfContents($project)
        ];

        $pdf = PDF::loadView('exports.project-idea-with-toc', $data);
        $pdf->setPaper('A4', 'portrait');

        // Options pour numérotation des pages
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        return $pdf->download("fiche_projet_{$project->identifiant_bip}.pdf");
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

        // Table des matières
        $tocSection = $phpWord->addSection(['breakType' => 'nextPage']);
        $this->addTableOfContents($tocSection);

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
        $tocStyle = ['tabLeader' => \PhpOffice\PhpWord\Style\TOC::TAB_LEADER_DOT, 'indentation' => ['left' => 200]];
        $phpWord->addStyle('TOC_Style', $tocStyle);
    }

    /**
     * Page de garde
     */
    private function addCoverPage($section, $project)
    {
        // Logo
        $section->addImage('images/benin-logo.png', [
            'width' => 150,
            'height' => 150,
            'alignment' => Jc::CENTER,
            'wrappingStyle' => 'inline'
        ]);

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
        $cell->addText($project->title, ['size' => 14, 'bold' => true], ['alignment' => Jc::CENTER]);

        $section->addTextBreak(3);

        // Numéro BIP et autres infos
        $infoTable = $section->addTable(['alignment' => Jc::CENTER]);
        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Numéro BIP:', ['bold' => true]);
        $infoTable->addCell(4500)->addText($project->identifiant_bip ?: 'Non attribué');

        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Coût estimé:', ['bold' => true]);
        $infoTable->addCell(4500)->addText(number_format($project->project_cost, 0, ',', ' ') . ' FCFA');

        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Date d\'élaboration:', ['bold' => true]);
        $infoTable->addCell(4500)->addText(now()->format('d/m/Y'));
    }

    /**
     * Ajouter la table des matières
     */
    private function addTableOfContents($section)
    {
        // Titre
        $section->addText(
            'TABLE DES MATIÈRES',
            ['name' => 'Arial', 'size' => 18, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
        );

        // Table des matières automatique
        $toc = $section->addTOC(
            ['name' => 'Arial', 'size' => 11],
            \PhpOffice\PhpWord\Style\TOC::TABLECONTENTS,
            1,  // Niveau min
            3   // Niveau max
        );

        $section->addTextBreak(2);

        // Note pour mise à jour
        $section->addText(
            'Note: Cliquez droit sur la table des matières dans Word et sélectionnez "Mettre à jour les champs" pour actualiser les numéros de page.',
            ['italic' => true, 'size' => 10, 'color' => '666666'],
            ['alignment' => Jc::LEFT]
        );
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
        $textBox->addText($project->title);

        // 1.2 Origine du projet
        $section->addTitle('2. Origine du projet', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->project_origin);

        // 1.3 Fondement
        $section->addTitle('3. Fondement', 2);
        $section->addText('(Action de la stratégie/Plan/Programme)', ['italic' => true, 'size' => 10]);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->foundation);

        // 1.4 Situation actuelle
        $section->addTitle('4. Situation actuelle', 2);
        $section->addText('(Problématique et/ou besoins)', ['italic' => true, 'size' => 10]);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->current_situation);

        // 1.5 Situation désirée
        $section->addTitle('5. Situation désirée', 2);
        $section->addText('(Finalité, Buts)', ['italic' => true, 'size' => 10]);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->desired_situation);

        // 1.6 Contraintes
        $section->addTitle('6. Contraintes à respecter et gérer', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->constraints);

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
        $textBox->addText($project->general_description);

        // 2.2 Échéancier
        $section->addTitle('2. Échéancier des principaux extrants', 2);
        $section->addText('(Indicateurs de réalisations physiques)', ['italic' => true, 'size' => 10]);

        if ($project->outputs_schedule) {
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
            $table->addRow();
            $table->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Extrant', ['bold' => true]);
            $table->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Date prévue', ['bold' => true]);
            $table->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Indicateur', ['bold' => true]);

            foreach (json_decode($project->outputs_schedule, true) as $output) {
                $table->addRow();
                $table->addCell(3000)->addText($output['name'] ?? '');
                $table->addCell(3000)->addText($output['date'] ?? '');
                $table->addCell(3000)->addText($output['indicator'] ?? '');
            }
        }

        // 2.3 Description des extrants
        $section->addTitle('3. Description des principaux extrants', 2);
        $section->addText('(spécifications techniques)', ['italic' => true, 'size' => 10]);

        if ($project->technical_specifications) {
            foreach (json_decode($project->technical_specifications, true) as $i => $spec) {
                $section->addTitle('3.' . ($i + 1) . ' ' . ($spec['title'] ?? 'Extrant ' . ($i + 1)), 3);
                $section->addText($spec['description'] ?? '');
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
        $textBox->addText($project->technical_characteristics ?? '');

        // 2.5 Localisation
        $section->addTitle('5. Localisation, choix du ou des site(s) d\'accueil et impact environnemental probable', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->location);

        // 2.6 Aspects organisationnels
        $section->addTitle('6. Aspects organisationnels du projet', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 100,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->organizational_aspects);

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

        if ($project->cost_benefits) {
            $costBenefits = json_decode($project->cost_benefits, true);

            // Tableau des coûts
            $section->addText('Estimation des coûts:', ['bold' => true]);
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
            $table->addRow();
            $table->addCell(4500, ['bgColor' => 'F2F2F2'])->addText('Poste de dépense', ['bold' => true]);
            $table->addCell(2000, ['bgColor' => 'F2F2F2'])->addText('Montant (FCFA)', ['bold' => true]);
            $table->addCell(2500, ['bgColor' => 'F2F2F2'])->addText('Pourcentage', ['bold' => true]);

            $totalCost = 0;
            foreach ($costBenefits['costs'] ?? [] as $cost) {
                $table->addRow();
                $table->addCell(4500)->addText($cost['item']);
                $table->addCell(2000)->addText(number_format($cost['amount'], 0, ',', ' '));
                $table->addCell(2500)->addText($cost['percentage'] . '%');
                $totalCost += $cost['amount'];
            }

            $table->addRow();
            $table->addCell(4500, ['bgColor' => 'E8E8E8'])->addText('TOTAL', ['bold' => true]);
            $table->addCell(2000, ['bgColor' => 'E8E8E8'])->addText(number_format($totalCost, 0, ',', ' '), ['bold' => true]);
            $table->addCell(2500, ['bgColor' => 'E8E8E8'])->addText('100%', ['bold' => true]);

            $section->addTextBreak();

            // Bénéfices attendus
            $section->addText('Bénéfices attendus:', ['bold' => true]);
            foreach ($costBenefits['benefits'] ?? [] as $benefit) {
                $section->addListItem($benefit, 0);
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
        $textBox->addText($project->immediate_risks);

        // 3.3 Conclusions et recommandations
        $section->addTitle('9. Conclusions et recommandations', 2);
        $textBox = $section->addTextBox([
            'width' => 450,
            'height' => 150,
            'borderSize' => 1,
            'borderColor' => '999999'
        ]);
        $textBox->addText($project->conclusions);

        // Solutions alternatives
        $section->addTitle('Autres solutions alternatives considérées et non retenues', 1);

        if ($project->alternative_solutions) {
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
            $table->addRow();
            $table->addCell(4500, ['bgColor' => 'F2F2F2'])->addText('Description sommaire', ['bold' => true]);
            $table->addCell(4500, ['bgColor' => 'F2F2F2'])->addText('Principales raisons du rejet', ['bold' => true]);

            foreach (json_decode($project->alternative_solutions, true) as $alternative) {
                $table->addRow();
                $table->addCell(4500)->addText($alternative['description'] ?? '');
                $table->addCell(4500)->addText($alternative['rejection_reason'] ?? '');
            }
        }

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
