<?php

namespace App\Services;

use App\Http\Resources\CanevasAppreciationTdrResource;
use App\Models\Projet;
use App\Models\Tdr;
use App\Repositories\DocumentRepository;
use Illuminate\Support\Facades\Storage;

class AppreciationTdrPrefaisabiliteExportService
{
    /**
     * Exporter l'appréciation du TDR de préfaisabilité vers Excel
     */
    public function export(Projet $projet): array
    {
        $tdrPrefaisabilite = $projet->tdrPrefaisabilite->first();//tdrs()->where('type', 'prefaisabilite')->first();

        if (!$tdrPrefaisabilite) {
            throw new \Exception("Le projet n'a pas de TDR de préfaisabilité");
        }

        // Récupérer l'évaluation terminée ou en cours
        $evaluationModel = $tdrPrefaisabilite->evaluationPrefaisabiliteTerminer() ?? $tdrPrefaisabilite->evaluationPrefaisabiliteEnCours();

        // Récupérer le canevas - d'abord depuis le TDR, puis depuis l'évaluation, puis depuis le modèle CanevasEvaluation
        $canevas = $tdrPrefaisabilite->canevas_appreciation_tdr;

        if (!$canevas && $evaluationModel && $evaluationModel->canevas) {
            $canevas = $evaluationModel->canevas;
        }

        if (!$canevas) {

            $canevasModel = (app(DocumentRepository::class)->getCanevasAppreciationTdrFaisabilite());

            if ($canevasModel) {
                $canevas = (new CanevasAppreciationTdrResource($canevasModel))->toArray(request());
            }

            elseif (!$canevasModel) {
                throw new \Exception("Aucun canevas d'appréciation trouvé pour le TDR de préfaisabilité");
            }
        }

        // Si l'évaluation existe, récupérer les données
        $evaluations = [];
        $resultatsEvaluation = [];
        if ($evaluationModel) {
            $evaluationData = $evaluationModel->evaluation ?? [];

            if (is_string($evaluationData)) {
                $decoded = json_decode($evaluationData, true);
                $evaluationData = is_array($decoded) ? $decoded : [];
            }
            if (!empty($evaluationData['champs_evalues'])) {
                foreach ($evaluationData['champs_evalues'] as $champ) {
                    $evaluations[$champ['attribut']] = [
                        'commentaire' => $champ['commentaire_evaluateur'] ?? '',
                        'appreciation' => $champ['appreciation'] ?? '',
                    ];
                }
            }
            $resultatsEvaluation = $evaluationModel->resultats_evaluation ?? [];
        }

        // Préparer les données pour le script Python
        $data = $this->prepareDataForExport($projet, $tdrPrefaisabilite, $canevas, $evaluations, $resultatsEvaluation);

        // Chemins
        $templatePath = base_path('canevas/O-2_Template_Appreciation_Prefaisabilite_Clean.xlsx'); // TODO: Use a specific template for TDR Prefaisabilite if available
        $pythonScript = base_path('scripts/generate_appreciation_excel.py');
        $identifiantBip = $projet->identifiant_bip ?? 'PROJET-' . $projet->id;
        $storageName = 'appreciation_tdr_prefaisabilite_' . $identifiantBip . '.xlsx';
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
            $storagePath = "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_prefaisabilite/tdr";
            Storage::makeDirectory($storagePath); // Ensure the directory exists
            $storedPath = "{$storagePath}/{$storageName}";
            Storage::disk('local')->put($storedPath, $fileContent);

            // Générer le hash d'accès
            $hashAcces = $this->generateFileAccessHash($projet->hashed_id, $storageName, 'appreciation-tdr-prefaisabilite');

            // Mettre à jour ou créer l'enregistrement dans la base de données
            $fichier = $tdrPrefaisabilite->fichiers()->updateOrCreate(
                [
                    'categorie' => 'appreciation_tdr_prefaisabilite',
                    'fichier_attachable_id' => $tdrPrefaisabilite->id,
                    'fichier_attachable_type' => Tdr::class,
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
                    'description' => 'Export Excel - Appréciation du TDR de Préfaisabilité',
                    'metadata' => [
                        'type_document' => 'appreciation-tdr-prefaisabilite',
                        'tdr_prefaisabilite_id' => $tdrPrefaisabilite->id,
                        'projet_id' => $projet->id,
                        'genere_par' => auth()->id() ?? 1,
                        'genere_le' => now(),
                    ],
                    'uploaded_by' => auth()->id() ?? 1,
                ]
            );

            return [
                'success' => true,
                'fichier_id' => $fichier->id,
                'storage_path' => $storedPath,
                'file_name' => $storageName,
                'size_formatted' => $this->formatBytes($fileSize),
                'md5' => $md5Hash,
            ];

        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            @unlink($jsonPath);
            @unlink($tempPath);
            throw $e;
        }
    }

    /**
     * Préparer les données pour l'export Excel (format JSON)
     */
    private function prepareDataForExport(Projet $projet, Tdr $tdrPrefaisabilite, array $canevas, array $evaluations, array $resultatsEvaluation): array
    {
        // Récupérer les informations du proposant/responsable
        $responsable = $projet->responsable;
        $ministere = $projet->ministere;

        // Coût du projet
        $coutEstimatif = '';
        if (!empty($projet->cout_estimatif_projet)) {
            $coutData = is_array($projet->cout_estimatif_projet) ? $projet->cout_estimatif_projet : json_decode($projet->cout_estimatif_projet, true);
            $montant = $coutData['montant'] ?? '';
            if ($montant) {
                $coutEstimatif = number_format($montant, 0, ',', ' ') . ' FCFA';
            }
        }

        $data = [
            'header' => [
                'titre_projet' => $projet->titre_projet ?? '',
                'identifiant_bip' => $projet->identifiant_bip ?? '',
                'cout_total' => $coutEstimatif,
                'date_demarrage' => $tdrPrefaisabilite->date_demarrage_etude ?? '',
                'date_achevement' => $tdrPrefaisabilite->date_achevement_etude ?? '',
            ],
            'accept_text' => $canevas['evaluation_configs']['accept_text'] ?? '',
            'proposant' => [
                'nom' => $responsable ? ($responsable->personne->nom . ' ' . $responsable->personne->prenom) : '',
                'telephone' => $responsable?->telephone ?? '',
                'email' => $responsable->email ?? '',
                'ministere' => $ministere->nom ?? '',
            ],
            'resultat_global' => $resultatsEvaluation['resultat_global'] ?? '',
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
     * Générer un hash d'accès sécurisé pour le fichier
     */
    private function generateFileAccessHash(string $projetId, string $storageName, string $category): string
    {
        return hash('sha256', $projetId . $storageName . $category . config('app.key'));
    }
}
