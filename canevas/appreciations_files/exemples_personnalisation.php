<?php
/**
 * Exemples de Personnalisation Avancée
 * 
 * Ce fichier montre comment étendre et personnaliser le générateur de template
 */

require 'vendor/autoload.php';
require 'generate_template.php';

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Exemple 1 : Ajouter un logo en en-tête
 */
class TemplateAvecLogo extends NoteConceptuelleTemplate
{
    public function ajouterLogo($cheminImage)
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo de l\'organisation');
        $drawing->setPath($cheminImage);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($this->sheet);
    }
}

/**
 * Exemple 2 : Personnaliser les couleurs selon le ministère
 */
class TemplateParMinistere extends NoteConceptuelleTemplate
{
    private $couleursMinistere = [
        'sante' => ['primary' => 'FF0066CC', 'secondary' => 'FFCCDDFF'],
        'education' => ['primary' => 'FF009900', 'secondary' => 'FFCCFFCC'],
        'infrastructure' => ['primary' => 'FFFF6600', 'secondary' => 'FFFFDDCC'],
        'default' => ['primary' => 'FF09A493', 'secondary' => 'FFEBFFFC']
    ];
    
    public function setMinistere($ministere)
    {
        $couleurs = $this->couleursMinistere[$ministere] ?? $this->couleursMinistere['default'];
        
        // Mettre à jour les constantes de couleur
        $this->updateColors($couleurs);
    }
    
    private function updateColors($couleurs)
    {
        // Appliquer les couleurs personnalisées aux sections
        // Cette méthode serait appelée avant la génération
    }
}

/**
 * Exemple 3 : Ajouter des sections personnalisées
 */
class TemplatePersonnalise extends NoteConceptuelleTemplate
{
    public function ajouterSectionAnalyseRisques($startRow)
    {
        $this->addSection($startRow, 'ANALYSE DES RISQUES', 'A:E', self::COLOR_TEAL);
        
        $risques = [
            'Risques techniques',
            'Risques financiers',
            'Risques environnementaux',
            'Risques sociaux',
            'Stratégies d\'atténuation'
        ];
        
        $currentRow = $startRow + 1;
        foreach ($risques as $risque) {
            $this->addSection($currentRow, $risque, 'A:D');
            $this->addValidationButton($currentRow + 1);
            $this->addGuide($currentRow);
            $currentRow += 2;
        }
        
        return $currentRow;
    }
}

/**
 * Exemple 4 : Exporter avec métadonnées personnalisées
 */
function genererAvecMetadonnees($titre, $auteur, $sujet)
{
    $generator = new NoteConceptuelleTemplate();
    $spreadsheet = $generator->spreadsheet;
    
    // Ajouter des métadonnées
    $properties = $spreadsheet->getProperties();
    $properties->setCreator($auteur)
               ->setLastModifiedBy($auteur)
               ->setTitle($titre)
               ->setSubject($sujet)
               ->setDescription("Outil d'évaluation de note conceptuelle")
               ->setKeywords("évaluation projet BIP gouvernement")
               ->setCategory("Gestion de projets");
    
    return $generator->generate("Evaluation_{$titre}.xlsx");
}

/**
 * Exemple 5 : Ajouter une feuille de calcul supplémentaire pour les statistiques
 */
class TemplateAvecStats extends NoteConceptuelleTemplate
{
    public function ajouterFeuilleStatistiques()
    {
        $statsSheet = $this->spreadsheet->createSheet();
        $statsSheet->setTitle('Statistiques');
        
        // Titre
        $statsSheet->setCellValue('A1', 'TABLEAU DE BORD DES ÉVALUATIONS');
        $statsSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $statsSheet->mergeCells('A1:E1');
        
        // En-têtes
        $headers = ['Catégorie', 'Total', 'Validés', 'Réservés', 'Rejetés'];
        foreach ($headers as $col => $header) {
            $colLetter = chr(65 + $col);
            $statsSheet->setCellValue("{$colLetter}3", $header);
            $statsSheet->getStyle("{$colLetter}3")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_TEAL]
                ]
            ]);
        }
        
        // Catégories
        $categories = [
            'Contexte et justification',
            'Objectifs',
            'Démarche de conduite',
            'Budget et financement'
        ];
        
        $row = 4;
        foreach ($categories as $categorie) {
            $statsSheet->setCellValue("A{$row}", $categorie);
            // Formules pour compter depuis la feuille principale
            $statsSheet->setCellValue("B{$row}", "=COUNTIF('Projet 1'!C:C,\"*\")");
            $statsSheet->setCellValue("C{$row}", "=COUNTIF('Projet 1'!C:C,\"Validé\")");
            $statsSheet->setCellValue("D{$row}", "=COUNTIF('Projet 1'!C:C,\"Réservé\")");
            $statsSheet->setCellValue("E{$row}", "=COUNTIF('Projet 1'!C:C,\"Rejeté\")");
            $row++;
        }
        
        // Total
        $statsSheet->setCellValue("A{$row}", 'TOTAL');
        $statsSheet->getStyle("A{$row}")->getFont()->setBold(true);
        $statsSheet->setCellValue("B{$row}", "=SUM(B4:B" . ($row-1) . ")");
        $statsSheet->setCellValue("C{$row}", "=SUM(C4:C" . ($row-1) . ")");
        $statsSheet->setCellValue("D{$row}", "=SUM(D4:D" . ($row-1) . ")");
        $statsSheet->setCellValue("E{$row}", "=SUM(E4:E" . ($row-1) . ")");
        
        // Graphique (nécessite plus de configuration)
        $this->ajouterGraphique($statsSheet);
    }
    
    private function ajouterGraphique($sheet)
    {
        // Créer un graphique camembert
        $dataSeriesLabels = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING,
                'Statistiques!$C$3', null, 1
            ),
        ];
        
        // Plus de configuration serait nécessaire pour un graphique complet
    }
}

/**
 * Exemple 6 : Validation conditionnelle avancée
 */
function ajouterValidationConditionnelle($sheet)
{
    // Mise en forme conditionnelle : vert si "Validé", rouge si "Rejeté"
    $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
    $conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
    $conditional1->setText('Validé');
    $conditional1->getStyle()->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FF00FF00']
        ]
    ]);
    
    $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
    $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
    $conditional2->setText('Rejeté');
    $conditional2->getStyle()->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFF0000']
        ]
    ]);
    
    $conditionalStyles = [$conditional1, $conditional2];
    $sheet->getStyle('C14:C44')->setConditionalStyles($conditionalStyles);
}

/**
 * Exemple 7 : Protection de feuille avec mot de passe
 */
function protegerFeuilleAvecMotDePasse($generator, $motDePasse = 'admin123')
{
    $sheet = $generator->sheet;
    
    // Déverrouiller les cellules de saisie
    $sheet->getStyle('B4:B8')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    $sheet->getStyle('B14:B44')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    $sheet->getStyle('C14:C44')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    $sheet->getStyle('D14:D44')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    
    // Protéger la feuille
    $sheet->getProtection()->setPassword($motDePasse);
    $sheet->getProtection()->setSheet(true);
    $sheet->getProtection()->setSort(true);
    $sheet->getProtection()->setInsertRows(true);
    $sheet->getProtection()->setFormatCells(true);
}

/**
 * Exemple 8 : Génération multiple pour plusieurs projets
 */
function genererPourPlusieursProjets($projets)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    foreach ($projets as $index => $nomProjet) {
        if ($index === 0) {
            $sheet = $spreadsheet->getActiveSheet();
        } else {
            $sheet = $spreadsheet->createSheet();
        }
        
        $sheet->setTitle(substr($nomProjet, 0, 31)); // Max 31 caractères
        
        // Générer le contenu pour ce projet
        // (Utiliser la logique de NoteConceptuelleTemplate adaptée)
    }
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('Evaluations_Multiples.xlsx');
}

/**
 * EXEMPLES D'UTILISATION
 */

// Exemple 1 : Avec logo
/*
$generator = new TemplateAvecLogo();
$generator->ajouterLogo('logo.png');
$generator->generate('template_avec_logo.xlsx');
*/

// Exemple 2 : Par ministère
/*
$generator = new TemplateParMinistere();
$generator->setMinistere('sante');
$generator->generate('template_sante.xlsx');
*/

// Exemple 3 : Avec section personnalisée
/*
$generator = new TemplatePersonnalise();
// La section serait ajoutée dans le processus de génération
$generator->generate('template_personnalise.xlsx');
*/

// Exemple 4 : Avec métadonnées
/*
genererAvecMetadonnees(
    'Évaluation Projet Infrastructure 2024',
    'Ministère des Travaux Publics',
    'Évaluation de note conceptuelle'
);
*/

// Exemple 5 : Avec statistiques
/*
$generator = new TemplateAvecStats();
$generator->ajouterFeuilleStatistiques();
$generator->generate('template_avec_stats.xlsx');
*/

// Exemple 6 : Avec validation conditionnelle
/*
$generator = new NoteConceptuelleTemplate();
ajouterValidationConditionnelle($generator->sheet);
$generator->generate('template_validation.xlsx');
*/

// Exemple 7 : Avec protection
/*
$generator = new NoteConceptuelleTemplate();
protegerFeuilleAvecMotDePasse($generator);
$generator->generate('template_protege.xlsx');
*/

// Exemple 8 : Pour plusieurs projets
/*
$projets = ['Projet A', 'Projet B', 'Projet C'];
genererPourPlusieursProjets($projets);
*/

echo "Fichier d'exemples de personnalisation créé!\n";
echo "Décommentez les exemples pour les tester.\n";
