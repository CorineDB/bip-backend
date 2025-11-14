<?php

namespace App\Services;

use App\Http\Resources\CanevasAppreciationRapportExAnteResource;
use App\Models\Projet;
use App\Models\Rapport;
use App\Repositories\DocumentRepository;
use Illuminate\Support\Facades\Storage;

class AppreciationRapportFinalExportService
{
    /**
     * Exporter l'appréciation du rapport final d'évaluation ex-ante vers Excel
     */
    public function export(Projet $projet): array
    {
        // Récupérer le dernier rapport d'évaluation ex-ante
        $rapport = \App\Models\Rapport::where('projet_id', $projet->id)
            ->where('type', 'evaluation_ex_ante')
            ->latest('created_at')
            ->first();

        if (!$rapport) {
            throw new \Exception("Le projet n'a pas de rapport d'évaluation ex-ante");
        }

        // Récupérer l'évaluation de validation finale
        $evaluationModel = $projet->evaluations()
            ->where('type_evaluation', 'validation-final-evaluation-ex-ante')
            ->where('statut', 1)
            ->latest('created_at')
            ->first();

        // Récupérer le canevas d'appréciation
        $canevasModel = app(DocumentRepository::class)->getModel()
            ->where('type', 'checklist')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-appreciation-rapport-finale'))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$canevasModel) {
            throw new \Exception("Aucun canevas d'appréciation trouvé pour le rapport final");
        }

        $canevas = (new CanevasAppreciationRapportExAnteResource($canevasModel))->toArray(request());

        // Si l'évaluation existe, récupérer les données
        $evaluations = [];
        $resultatsEvaluation = [];
        if ($evaluationModel) {
            $evaluationData = $evaluationModel->evaluation ?? [];

            if (is_string($evaluationData)) {
                $decoded = json_decode($evaluationData, true);
                $evaluationData = is_array($decoded) ? $decoded : [];
            }

            // Récupérer les évaluations de champs depuis les données de validation
            if (!empty($evaluationData['evaluations_champs'])) {
                foreach ($evaluationData['evaluations_champs'] as $champ) {
                    // Trouver l'attribut du champ depuis le canevas
                    $champCanevas = collect($canevas['all_champs'])->firstWhere('id', $champ['champ_id']);
                    if ($champCanevas) {
                        $evaluations[$champCanevas['attribut']] = [
                            'commentaire' => $champ['commentaire'] ?? '',
                            'appreciation' => $champ['appreciation'] ?? '',
                        ];
                    }
                }
            }

            $resultatsEvaluation = [
                'action' => $evaluationData['action'] ?? 'valider',
                'commentaire' => $evaluationData['commentaire'] ?? '',
                'date_validation' => $evaluationModel->valider_le ?? now(),
            ];
        }

        // Préparer les données pour le script Python
        $data = $this->prepareDataForExport($projet, $rapport, $canevas, $evaluations, $resultatsEvaluation);

        // Chemins
        $templatePath = base_path('canevas/O-2_Template_Appreciation_Prefaisabilite_Clean.xlsx'); // Utiliser le template générique
        $pythonScript = base_path('scripts/generate_appreciation_excel.py');
        $identifiantBip = $projet->identifiant_bip ?? 'PROJET-' . $projet->id;
        $storageName = 'appreciation_rapport_final_' . $identifiantBip . '.xlsx';
        $tempPath = storage_path('app/temp/' . $storageName);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Créer un fichier JSON temporaire
        $jsonPath = storage_path('app/temp/appreciation_data_' . $projet->id . '.json');
        file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        try {
            // Exécuter le script Python pour générer le fichier dans le répertoire temporaire
            $command = sprintf(
                'python3 %s %s %s %s 2>&1',
                escapeshellarg($pythonScript),
                escapeshellarg($jsonPath),
                escapeshellarg($templatePath),
                escapeshellarg($tempPath)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("Erreur lors de l'exécution du script Python: " . implode("\n\n\n", $output));
            }

            // Lire le contenu du fichier généré
            $fileContent = file_get_contents($tempPath);
            $fileSize = filesize($tempPath);
            $md5Hash = md5_file($tempPath);

            // Supprimer les fichiers temporaires
            @unlink($jsonPath);
            @unlink($tempPath);

            // Stocker le fichier final
            $hashedIdentifiantBip = hash('sha256', $identifiantBip);
            $storagePath = "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/rapport_final";
            Storage::makeDirectory($storagePath);
            $storedPath = "{$storagePath}/{$storageName}";
            Storage::disk('local')->put($storedPath, $fileContent);

            // Générer le hash d'accès
            $hashAcces = $this->generateFileAccessHash($projet->hashed_id, $storageName, 'appreciation-rapport-final');

            // Mettre à jour ou créer l'enregistrement dans la base de données
            $fichier = $rapport->fichiers()->updateOrCreate(
                [
                    'categorie' => 'appreciation_rapport_final',
                    'fichier_attachable_id' => $rapport->id,
                    'fichier_attachable_type' => Rapport::class,
                ],
                [
                    'nom_original' => $storageName,
                    'nom_stockage' => $storageName,
                    'chemin' => $storedPath,
                    'extension' => 'xlsx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'taille' => $fileSize,
                    'hash_md5' => $md5Hash,
                    'hash_acces' => $hashAcces,
                    'description' => 'Export Excel - Appréciation du Rapport Final d\'Évaluation Ex-Ante',
                    'metadata' => [
                        'type_document' => 'appreciation-rapport-final',
                        'rapport_id' => $rapport->id,
                        'projet_id' => $projet->id,
                        'genere_par' => auth()->id() ?? 1,
                        'genere_le' => now(),
                    ],
                    'uploaded_by' => auth()->id() ?? 1,
                ]
            );

            return [
                'success' => true,
                'file_name' => $storageName,
                'file_path' => $storedPath,
                'size' => $fileSize,
                'size_formatted' => $this->formatBytes($fileSize),
                'fichier_id' => $fichier->id,
            ];
        } catch (\Exception $e) {
            // Nettoyer les fichiers temporaires en cas d'erreur
            @unlink($jsonPath);
            @unlink($tempPath);
            throw $e;
        }
    }

    /**
     * Préparer les données pour l'export Excel
     */
    private function prepareDataForExport(Projet $projet, Rapport $rapport, array $canevas, array $evaluations, array $resultatsEvaluation): array
    {
        $ideeProjet = $projet->ideeProjet;

        return [
            'projet' => [
                'identifiant_bip' => $ideeProjet->identifiant_bip ?? '',
                'intitule' => $ideeProjet->intitule ?? '',
                'ministere' => $ideeProjet->ministere->nom ?? '',
                'secteur' => $ideeProjet->secteur->nom ?? '',
            ],
            'rapport' => [
                'intitule' => $rapport->intitule ?? '',
                'type' => $rapport->type ?? '',
                'date_soumission' => $rapport->date_soumission ? $rapport->date_soumission->format('d/m/Y') : '',
                'date_validation' => $rapport->date_validation ? $rapport->date_validation->format('d/m/Y') : '',
            ],
            'canevas' => $canevas,
            'evaluations' => $evaluations,
            'resultats' => $resultatsEvaluation,
        ];
    }

    /**
     * Générer un hash d'accès sécurisé pour le fichier
     */
    private function generateFileAccessHash(string $projetHashedId, string $fileName, string $type): string
    {
        return hash('sha256', $projetHashedId . $fileName . $type . config('app.key'));
    }

    /**
     * Formater la taille du fichier en format lisible
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
}
