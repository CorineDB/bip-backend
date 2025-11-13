<?php
/**
 * Exemple simplifié de génération du template
 * 
 * Ce script montre comment utiliser le générateur de manière simple
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Fonction simplifiée pour créer un template basique
 */
function creerTemplateSimple($nomFichier = 'template_simple.xlsx')
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Évaluation Projet');
    
    // Configuration des colonnes
    $sheet->getColumnDimension('A')->setWidth(40);
    $sheet->getColumnDimension('B')->setWidth(35);
    $sheet->getColumnDimension('C')->setWidth(20);
    
    // Titre principal
    $sheet->setCellValue('A1', 'OUTIL D\'ÉVALUATION DE PROJET');
    $sheet->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 16],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);
    $sheet->mergeCells('A1:C1');
    
    // Informations de base
    $infos = [
        3 => ['label' => 'Titre du projet:', 'value' => ''],
        4 => ['label' => 'Numéro BIP:', 'value' => ''],
        5 => ['label' => 'Coût:', 'value' => ''],
        6 => ['label' => 'Date de début:', 'value' => ''],
    ];
    
    foreach ($infos as $row => $data) {
        $sheet->setCellValue("A{$row}", $data['label']);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("B{$row}", $data['value']);
    }
    
    // En-têtes des colonnes d'évaluation
    $row = 8;
    $headers = ['Question', 'Réponse', 'Statut'];
    foreach ($headers as $col => $header) {
        $colLetter = chr(65 + $col); // A, B, C
        $sheet->setCellValue("{$colLetter}{$row}", $header);
        $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CCCCCC']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
    }
    
    // Questions d'évaluation
    $questions = [
        'Le contexte du projet est-il clairement défini?',
        'Les objectifs sont-ils mesurables?',
        'Le budget est-il réaliste?',
        'Les parties prenantes sont-elles identifiées?',
        'Le chronogramme est-il détaillé?'
    ];
    
    $currentRow = 9;
    foreach ($questions as $question) {
        $sheet->setCellValue("A{$currentRow}", $question);
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($currentRow)->setRowHeight(30);
        
        // Ajouter une liste déroulante pour le statut
        $validation = $sheet->getCell("C{$currentRow}")->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('"Validé,Réservé,Rejeté"');
        $validation->setShowDropDown(true);
        
        $currentRow++;
    }
    
    // Section résultats
    $resultsRow = $currentRow + 2;
    $sheet->setCellValue("A{$resultsRow}", 'RÉSULTATS');
    $sheet->getStyle("A{$resultsRow}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFF00']
        ]
    ]);
    $sheet->mergeCells("A{$resultsRow}:C{$resultsRow}");
    
    // Compteurs
    $resultsRow++;
    $sheet->setCellValue("A{$resultsRow}", 'Nombre de validations:');
    $sheet->setCellValue("B{$resultsRow}", '=COUNTIF(C9:C13,"Validé")');
    $sheet->getStyle("A{$resultsRow}")->getFont()->setBold(true);
    
    $resultsRow++;
    $sheet->setCellValue("A{$resultsRow}", 'Taux de validation:');
    $sheet->setCellValue("B{$resultsRow}", '=IF(COUNTA(C9:C13)>0,COUNTIF(C9:C13,"Validé")/COUNTA(C9:C13),0)');
    $sheet->getStyle("B{$resultsRow}")->getNumberFormat()->setFormatCode('0.00%');
    $sheet->getStyle("A{$resultsRow}")->getFont()->setBold(true);
    
    // Bordures
    $sheet->getStyle("A8:C" . ($currentRow - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]
    ]);
    
    // Sauvegarder
    $writer = new Xlsx($spreadsheet);
    $writer->save($nomFichier);
    
    return $nomFichier;
}

// Exécution
try {
    echo "Génération du template simplifié...\n";
    $fichier = creerTemplateSimple();
    echo "✓ Fichier créé avec succès: {$fichier}\n";
    echo "\nOuvrez le fichier Excel pour voir le résultat!\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
