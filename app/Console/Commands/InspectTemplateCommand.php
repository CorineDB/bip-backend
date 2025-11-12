<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InspectTemplateCommand extends Command
{
    protected $signature = 'template:inspect {file} {--sheet=all}';
    protected $description = 'Inspect Excel template structure';

    public function handle()
    {
        $file = $this->argument('file');
        $sheetOption = $this->option('sheet');
        $filePath = base_path($file);

        if (!file_exists($filePath)) {
            $this->error("Fichier non trouvé: {$filePath}");
            return 1;
        }

        $this->info("Inspection du fichier: {$filePath}");

        try {
            $spreadsheet = IOFactory::load($filePath);

            // Afficher toutes les feuilles disponibles
            $this->info("\n=== Feuilles disponibles ===");
            $sheetNames = $spreadsheet->getSheetNames();
            foreach ($sheetNames as $index => $name) {
                $this->line("Feuille {$index}: {$name}");
            }

            // Inspecter les feuilles selon l'option
            if ($sheetOption === 'all') {
                foreach ($sheetNames as $index => $name) {
                    $this->inspectSheet($spreadsheet->getSheet($index), $index);
                }
            } else {
                $sheetIndex = is_numeric($sheetOption) ? (int)$sheetOption : 0;
                $this->inspectSheet($spreadsheet->getSheet($sheetIndex), $sheetIndex);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function inspectSheet($sheet, $sheetIndex)
    {
        $this->info("\n" . str_repeat("=", 80));
        $this->info("=== FEUILLE {$sheetIndex}: {$sheet->getTitle()} ===");
        $this->info(str_repeat("=", 80));

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $this->info("Dimension: {$highestRow} lignes × {$highestColumn} colonnes");

        $this->info("\n--- Contenu des cellules (lignes avec données) ---");

        // Afficher les 50 premières lignes avec contenu
        $rowCount = 0;
        for ($row = 1; $row <= min($highestRow, 100) && $rowCount < 50; $row++) {
            $hasContent = false;
            $rowData = [];

            for ($col = 'A'; $col <= 'M'; $col++) {
                $cell = $col . $row;
                $value = $sheet->getCell($cell)->getValue();

                if (!empty($value)) {
                    $hasContent = true;

                    // Limiter l'affichage des valeurs longues
                    if (is_string($value)) {
                        $displayValue = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
                    } else {
                        $displayValue = $value;
                    }

                    $rowData[$col] = $displayValue;
                }
            }

            if ($hasContent) {
                $this->line("\nLigne {$row}:");
                foreach ($rowData as $col => $value) {
                    $this->line("  {$col}{$row}: {$value}");
                }
                $rowCount++;
            }
        }

        // Afficher les cellules fusionnées
        $this->info("\n--- Cellules fusionnées ---");
        $mergedCells = $sheet->getMergeCells();
        if (!empty($mergedCells)) {
            foreach ($mergedCells as $range) {
                $this->line("  {$range}");
            }
        } else {
            $this->line("  Aucune cellule fusionnée");
        }
    }
}
