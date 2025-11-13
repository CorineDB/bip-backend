<?php
/**
 * Générateur de template Excel - Outil d'évaluation de la note conceptuelle
 * 
 * Ce script génère un fichier Excel formaté pour l'évaluation de notes conceptuelles de projets
 * 
 * Installation requise: composer require phpoffice/phpspreadsheet
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class NoteConceptuelleTemplate
{
    private $spreadsheet;
    private $sheet;
    
    // Couleurs utilisées dans le template
    const COLOR_RED = 'FFC00000';
    const COLOR_LIGHT_BLUE = 'FFEBFFFC';
    const COLOR_TEAL = 'FF09A493';
    const COLOR_WHITE = 'FFFFFFFF';
    const COLOR_BLACK = 'FF000000';
    
    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('Projet 1');
    }
    
    /**
     * Configure les largeurs de colonnes
     */
    private function setupColumnWidths()
    {
        $this->sheet->getColumnDimension('A')->setWidth(42.0);
        $this->sheet->getColumnDimension('B')->setWidth(38.63);
        $this->sheet->getColumnDimension('C')->setWidth(19.25);
        $this->sheet->getColumnDimension('D')->setWidth(20.38);
        $this->sheet->getColumnDimension('E')->setWidth(13.75);
        $this->sheet->getColumnDimension('F')->setWidth(8.75);
        $this->sheet->getColumnDimension('G')->setWidth(13.0);
        $this->sheet->getColumnDimension('H')->setWidth(13.0);
        $this->sheet->getColumnDimension('I')->setWidth(13.0);
    }
    
    /**
     * Crée l'en-tête du document
     */
    private function createHeader()
    {
        // Titre principal
        $this->sheet->setCellValue('A2', 'NOTE CONCEPTUELLE DE PROJET');
        $this->sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16
            ]
        ]);
        $this->sheet->mergeCells('A2:E2');
        
        // Informations du projet
        $headers = [
            'A4' => 'Titre du projet',
            'A5' => 'Numéro d\'identification BIP',
            'A6' => 'Coût du projet',
            'A7' => 'Date de démarrage de l\'étude',
            'A8' => 'Date d\'achèvement de l\'étude'
        ];
        
        foreach ($headers as $cell => $value) {
            $this->sheet->setCellValue($cell, $value);
            $this->sheet->getStyle($cell)->getFont()->setBold(true);
            $this->sheet->mergeCells($cell . ':B' . substr($cell, 1));
        }
    }
    
    /**
     * Crée la ligne d'instructions (ligne 11)
     */
    private function createInstructionsRow()
    {
        // Colonne A - Instructions générales
        $this->sheet->setCellValue('A11', 'Toutes les questions ci-dessous doit avoir une réponse !');
        $this->sheet->mergeCells('A11:B11');
        $this->sheet->getStyle('A11')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Colonne C - Évaluation
        $this->sheet->setCellValue('C11', 'Évaluation : cette colonne doit être complétée par l\'évaluateur seulement');
        $this->sheet->getStyle('C11')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_RED]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);
        
        // Colonne D - Commentaires
        $this->sheet->setCellValue('D11', 'Commentaires');
        $this->sheet->getStyle('D11')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_LIGHT_BLUE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Colonne E - Guide de notation
        $this->sheet->setCellValue('E11', 'Guide de notation');
        $this->sheet->getStyle('E11')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_LIGHT_BLUE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
    }
    
    /**
     * Ajoute une section avec validation
     */
    private function addSection($row, $questionText, $mergeCells = 'A', $bgColor = null, $isBold = true)
    {
        // Question principale
        $cellA = "A{$row}";
        $this->sheet->setCellValue($cellA, $questionText);
        
        if ($mergeCells !== false) {
            $this->sheet->mergeCells("{$cellA}:B{$row}");
        }
        
        $styleArray = [
            'font' => ['bold' => $isBold],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true
            ]
        ];
        
        if ($bgColor) {
            $styleArray['fill'] = [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $bgColor]
            ];
        }
        
        $this->sheet->getStyle($cellA)->applyFromArray($styleArray);
    }
    
    /**
     * Ajoute un bouton de validation
     */
    private function addValidationButton($row, $guideRow = null)
    {
        $cellC = "C{$row}";
        $this->sheet->setCellValue($cellC, '[ Valider le statut ]');
        
        $mergeRow = $guideRow ?: $row;
        if ($row != $mergeRow) {
            $this->sheet->mergeCells("C{$row}:C{$mergeRow}");
        }
        
        $this->sheet->getStyle($cellC)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_LIGHT_BLUE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
    }
    
    /**
     * Ajoute le guide de notation
     */
    private function addGuide($row, $mergeEndRow = null)
    {
        $guideText = "Validé : L'élément est accepté et passe à l'étape suivante \n\n" .
                     "Réservé : L'élément nécessite une amélioration avant validation \n\n" .
                     "Rejeté : L'élément ne correspond pas aux attentes";
        
        $cellE = "E{$row}";
        $this->sheet->setCellValue($cellE, $guideText);
        
        if ($mergeEndRow) {
            $this->sheet->mergeCells("E{$row}:E{$mergeEndRow}");
        }
        
        $this->sheet->getStyle($cellE)->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true
            ]
        ]);
    }
    
    /**
     * Crée le contenu principal du formulaire
     */
    private function createMainContent()
    {
        // Section 1: Contexte et justification
        $this->addSection(13, 'Contexte et justification', 'A:E', self::COLOR_LIGHT_BLUE);
        
        $contextQuestion = "Comment le projet répondrait-il aux objectifs spécifiques du Plan national de développement, " .
                          "du Plan sectoriel, de la Stratégie de croissance verte et de résilience climatique ou de toute autre " .
                          "politique gouvernementale ? (Votre réponse doit inclure des références à des programmes spécifiques et inclure " .
                          "des références de documents spécifiques, avec le numéro de page et le paragraphe pertinents. Des critères politiques " .
                          "spécifiques tels que la création d'emplois, le genre et le changement climatique doivent également être mentionnés ici. " .
                          "Les questions liées au changement climatique en particulier sont une caractéristique de plus en plus importante des " .
                          "priorités de dépenses d'investissement du gouvernement, cela doit donc être pris en compte dans votre réponse. " .
                          "Indiquez si le projet est lié aux contributions déterminées au niveau national)";
        
        $this->addSection(14, $contextQuestion, 'A:B', null, false);
        $this->addValidationButton(14);
        $this->addGuide(14);
        
        // Section 2: Objectif général du projet
        $this->addSection(16, 'Objectif général du projet', 'A:B');
        $this->addValidationButton(16, 17);
        $this->addGuide(16, 17);
        
        // Section 3: Objectifs spécifiques
        $this->addSection(19, 'Objectifs spécifiques', 'A:B', self::COLOR_LIGHT_BLUE);
        $this->addValidationButton(19, 20);
        $this->addGuide(19, 20);
        
        // Section 4: Résultats attendus du projet
        $this->addSection(21, 'Résultats attendus du projet', 'A:B');
        $this->addValidationButton(21, 22);
        $this->addGuide(21, 22);
        
        // Section principale: Démarche de conduite
        $this->addSection(23, 'Démarche de conduite du processus d\'élaboration du projet', 'A:E', self::COLOR_TEAL);
        
        // Sous-sections de la démarche
        $demarches = [
            24 => 'Démarche administrative',
            26 => 'Démarche technique',
            28 => 'Parties prenantes',
            30 => 'Les livrables du processus d\'élaboration du projet',
            32 => 'Cohérence du projet avec le PAG ou la stratégie sectorielle',
            34 => 'Pilotage et gouvernance du projet',
            36 => 'Chronogramme du processus'
        ];
        
        foreach ($demarches as $row => $text) {
            $bgColor = ($row == 24 || $row == 26 || $row == 28) ? null : self::COLOR_TEAL;
            $this->addSection($row, $text, 'A:D', $bgColor);
            $this->addValidationButton($row + 1);
            $this->addGuide($row);
        }
        
        // Section Budget
        $this->addSection(38, 'Budget et sources de financement du projet', 'A:E', self::COLOR_TEAL);
        
        $budgetSections = [
            39 => 'Budget détaillé du processus',
            41 => 'Coût estimatif du projet',
            43 => 'Sources de financement'
        ];
        
        foreach ($budgetSections as $row => $text) {
            $this->addSection($row, $text, 'A:D');
            $this->addValidationButton($row + 1);
            $this->addGuide($row, $row + 1);
        }
    }
    
    /**
     * Crée la section de signature
     */
    private function createSignatureSection()
    {
        // Message de soumission
        $submitText = "En remplissant et en transmettant cette note conceptuelle de projet, je confirme que je suis autorisé(e) " .
                     "à le faire par le responsable de notre direction/institution, et que toutes les informations qu'elle contient " .
                     "sont exactes. Je comprends que je suis personnellement responsable de l'exactitude des informations fournies.";
        
        $this->sheet->setCellValue('A45', $submitText);
        $this->sheet->mergeCells('A45:E45');
        $this->sheet->getStyle('A45')->getAlignment()->setWrapText(true);
        
        // En-tête proposant
        $this->sheet->setCellValue('A46', 'À remplir par le proposant du projet');
        $this->sheet->mergeCells('A46:E46');
        $this->sheet->getStyle('A46')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_LIGHT_BLUE]
            ]
        ]);
        
        // Informations du proposant
        $this->sheet->setCellValue('A47', 'Proposition de projet préparée par (Nom) :');
        $this->sheet->setCellValue('B47', 'Téléphone:');
        $this->sheet->setCellValue('C47', 'E-mail:');
        $this->sheet->getStyle('A47:C47')->getFont()->setBold(true);
        
        $this->sheet->setCellValue('A48', 'Nom du ministère :');
        $this->sheet->mergeCells('A48:C48');
        $this->sheet->getStyle('A48')->getFont()->setBold(true);
    }
    
    /**
     * Crée la section de résultats
     */
    private function createResultsSection()
    {
        // En-tête des résultats
        $this->sheet->setCellValue('A49', 'Résultats de l\'examen');
        $this->sheet->mergeCells('A49:E49');
        $this->sheet->getStyle('A49')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_TEAL]
            ]
        ]);
        
        // Compteur de rubriques validées
        $this->sheet->setCellValue('A50', 'Nombre de rubriques validées');
        $this->sheet->setCellValue('B50', '=COUNTIF(C$14:C$44,"Validé")');
        $this->sheet->getStyle('A50:B50')->getFont()->setBold(true);
        
        // Autres lignes de résultats
        $resultRows = [
            51 => 'Nombre de rubriques réservées',
            52 => 'Nombre de rubriques rejetées',
            53 => 'Total',
            55 => 'Pourcentage de validation'
        ];
        
        foreach ($resultRows as $row => $text) {
            $this->sheet->setCellValue("A{$row}", $text);
            $this->sheet->getStyle("A{$row}")->getFont()->setBold(true);
        }
        
        // Formules
        $this->sheet->setCellValue('B51', '=COUNTIF(C$14:C$44,"Réservé")');
        $this->sheet->setCellValue('B52', '=COUNTIF(C$14:C$44,"Rejeté")');
        $this->sheet->setCellValue('B53', '=B50+B51+B52');
        $this->sheet->setCellValue('B55', '=IF(B53>0,B50/B53,0)');
        $this->sheet->getStyle('B55')->getNumberFormat()->setFormatCode('0.00%');
    }
    
    /**
     * Applique les bordures
     */
    private function applyBorders()
    {
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        
        // Bordures pour les sections principales
        $this->sheet->getStyle('A4:B8')->applyFromArray($borderStyle);
        $this->sheet->getStyle('A11:E11')->applyFromArray($borderStyle);
        $this->sheet->getStyle('A13:E44')->applyFromArray($borderStyle);
        $this->sheet->getStyle('A46:E48')->applyFromArray($borderStyle);
        $this->sheet->getStyle('A49:E55')->applyFromArray($borderStyle);
    }
    
    /**
     * Configure les listes déroulantes de validation
     */
    private function setupValidationLists()
    {
        $validation = $this->sheet->getCell('C14')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Erreur de saisie');
        $validation->setError('Veuillez choisir une valeur dans la liste');
        $validation->setPromptTitle('Statut de validation');
        $validation->setPrompt('Choisissez: Validé, Réservé ou Rejeté');
        $validation->setFormula1('"Validé,Réservé,Rejeté"');
        
        // Appliquer la validation à toutes les cellules d'évaluation
        for ($row = 14; $row <= 44; $row++) {
            if (in_array($row, [14, 16, 19, 21, 25, 27, 29, 31, 33, 35, 37, 40, 42, 44])) {
                $this->sheet->getCell("C{$row}")->setDataValidation(clone $validation);
            }
        }
    }
    
    /**
     * Génère le fichier Excel complet
     */
    public function generate($filename = 'O-5_Outil_evaluation_note_conceptuelle.xlsx')
    {
        $this->setupColumnWidths();
        $this->createHeader();
        $this->createInstructionsRow();
        $this->createMainContent();
        $this->createSignatureSection();
        $this->createResultsSection();
        $this->applyBorders();
        $this->setupValidationLists();
        
        // Protéger certaines cellules (optionnel)
        $this->sheet->getProtection()->setSheet(false);
        
        // Enregistrer le fichier
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filename);
        
        return $filename;
    }
    
    /**
     * Télécharge le fichier directement dans le navigateur
     */
    public function download($filename = 'O-5_Outil_evaluation_note_conceptuelle.xlsx')
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

// Utilisation du générateur
try {
    $generator = new NoteConceptuelleTemplate();
    
    // Option 1: Générer et sauvegarder le fichier
    $filename = $generator->generate();
    echo "Fichier généré avec succès : {$filename}\n";
    
    // Option 2: Pour télécharger directement dans un contexte web
    // $generator->download();
    
} catch (Exception $e) {
    echo "Erreur lors de la génération : " . $e->getMessage() . "\n";
}
