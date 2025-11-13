<?php

namespace App\Services;

use App\Models\Projet;
use App\Models\NoteConceptuelle;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

class NoteConceptuelleExportService
{
    /**
     * Exporter la note conceptuelle d'un projet vers un fichier DOCX
     *
     * @param Projet $projet
     * @return array
     * @throws \Exception
     */
    public function exportNoteConceptuelle(Projet $projet): array
    {
        // Vérifier que le projet a une note conceptuelle
        $noteConceptuelle = $projet->noteConceptuelle;

        if (!$noteConceptuelle || empty($noteConceptuelle->note_conceptuelle)) {
            throw new \Exception("Aucune note conceptuelle trouvée pour ce projet");
        }

        // Charger le template
        $templatePath = base_path('canevas/O-4_Canevas de la note conceptuelle.docx');

        if (!file_exists($templatePath)) {
            throw new \Exception("Template de note conceptuelle introuvable: {$templatePath}");
        }

        // Générer le nom du fichier
        $identifiantBip = $projet->identifiant_bip ?? 'PROJET-' . $projet->id;
        $timestamp = time();
        $storageName = "note_conceptuelle_{$projet->identifiant_bip}.docx";
        $tempPath = storage_path('app/temp/' . $storageName);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Copier le template
        copy($templatePath, $tempPath);

        // Remplir le template avec TemplateProcessor
        $templateProcessor = new TemplateProcessor($tempPath);

        // Remplir les informations du projet (en-tête)
        $this->fillProjectInfo($templateProcessor, $projet);

        // Remplir les données de la note conceptuelle (contenu)
        $this->fillNoteConceptuelleData($templateProcessor, $noteConceptuelle);

        // Sauvegarder
        $templateProcessor->saveAs($tempPath);

        // Lire le contenu
        $fileContent = file_get_contents($tempPath);
        $fileSize = filesize($tempPath);
        $hashMd5 = md5_file($tempPath);

        // Supprimer le fichier temporaire
        unlink($tempPath);

        // Hasher l'identifiant BIP pour le stockage
        $hashedIdentifiantBip = hash('sha256', $identifiantBip);

        // Stocker le fichier
        $storagePath = "projets/{$hashedIdentifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle";
        $storedPath = "{$storagePath}/{$storageName}";
        Storage::disk('local')->put($storedPath, $fileContent);

        // Générer le hash d'accès
        $hashAcces = $this->generateFileAccessHash($projet->hashed_id, $storageName, 'note-conceptuelle-export');

        // Vérifier si un export existe déjà pour ce projet
        $existingFile = $noteConceptuelle->fichiers()
            ->where('categorie', 'note-conceptuelle-export')
            ->where('fichier_attachable_type', Projet::class)
            ->first();

        if ($existingFile) {
            // Supprimer l'ancien fichier physique
            $oldFilePath = storage_path("app/{$existingFile->chemin}");
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Supprimer l'entrée de la base de données
            $existingFile->delete();
        }

        // Créer l'entrée dans la table fichiers
        $fichier = $noteConceptuelle->fichiers()->create([
            'nom_original' => "note_conceptuelle_{$identifiantBip}.docx",
            'nom_stockage' => $storageName,
            'chemin' => $storedPath,
            'extension' => 'docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'taille' => $fileSize,
            'hash_md5' => $hashMd5,
            'hash_acces' => $hashAcces,
            'description' => 'Export DOCX - Note Conceptuelle',
            'commentaire' => null,
            'metadata' => [
                'type_document' => 'note-conceptuelle-export',
                'note_conceptuelle_id' => $noteConceptuelle->id,
                'projet_id' => $projet->id,
                'categorie_originale' => 'note-conceptuelle-export',
                'genere_par' => $projet->responsableId ?? auth()->id(),
                'genere_le' => now(),
                'dossier_public' => "Projets/{$identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle"
            ],
            'fichier_attachable_id' => $noteConceptuelle->id,
            'fichier_attachable_type' => NoteConceptuelle::class,
            'categorie' => 'note-conceptuelle-export',
            'ordre' => 1,
            'est_publie' => false,
            'est_actif' => true,
            'uploaded_by' => $projet->responsableId ?? auth()->id(),
        ]);

        return [
            'success' => true,
            'fichier_id' => $fichier->id,
            'fichier_hashed_id' => $fichier->hashed_id ?? null,
            'storage_path' => $storedPath,
            'file_name' => $storageName,
            'original_name' => "note_conceptuelle_{$identifiantBip}.docx",
            'size' => $fileSize,
            'size_formatted' => $this->formatBytes($fileSize),
            'md5' => $hashMd5,
            'hash_acces' => $hashAcces,
            'dossier_public' => "Projets/{$identifiantBip}/evaluation_ex_ante/etude_profil/note_conceptuelle"
        ];
    }

    /**
     * Remplir les informations du projet dans le template (en-tête)
     */
    private function fillProjectInfo(TemplateProcessor $templateProcessor, Projet $projet): void
    {
        // Titre du projet
        $templateProcessor->setValue('titre_projet', $projet->titre_projet ?? '');

        // Origine du projet (ministère)
        $templateProcessor->setValue('origine_projet', $projet->ministere->nom ?? '');

        // Identifiant BIP
        $templateProcessor->setValue('identifiant_bip', $projet->identifiant_bip ?? '');

        // Coût du projet
        if (!empty($projet->cout_estimatif_projet)) {
            $coutEstimatif = is_array($projet->cout_estimatif_projet)
                ? ($projet->cout_estimatif_projet['montant'] ?? '')
                : $projet->cout_estimatif_projet;

            if (!empty($coutEstimatif)) {
                $templateProcessor->setValue('cout_projet', number_format($coutEstimatif, 0, ',', ' ') . ' FCFA');
            } else {
                $templateProcessor->setValue('cout_projet', '');
            }
        } else {
            $templateProcessor->setValue('cout_projet', '');
        }

        // Date de démarrage
        $dateDebut = $projet->date_debut_etude;
        if ($dateDebut) {
            if (is_string($dateDebut)) {
                $dateDebut = \Carbon\Carbon::parse($dateDebut);
            }
            $templateProcessor->setValue('date_demarrage', $dateDebut->format('d/m/Y'));
        } else {
            $templateProcessor->setValue('date_demarrage', '');
        }

        // Décision
        $decision = $projet->decision ?? '';
        if (is_array($decision)) {
            $decision = json_encode($decision, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        $templateProcessor->setValue('decision', $decision);
    }

    /**
     * Remplir les données de la note conceptuelle
     */
    private function fillNoteConceptuelleData(TemplateProcessor $templateProcessor, NoteConceptuelle $noteConceptuelle): void
    {
        // Créer un mapping des données par attribut
        $dataMap = [];
        foreach ($noteConceptuelle->note_conceptuelle as $item) {
            $attribut = $item['attribut'] ?? '';
            if (!empty($attribut)) {
                $dataMap[$attribut] = $item['valeur'] ?? '';
            }
        }

        // Liste des attributs à remplir (basé sur le canevas)
        $attributes = [
            'contexte_justification',
            'objectifs_projet',
            'resultats_attendus',
            'demarche_administrative',
            'demarche_technique',
            'parties_prenantes',
            'livrables_processus',
            'coherence_strategique',
            'pilotage_gouvernance',
            'chronogramme_processus',
            'budget_detaille',
            'cout_estimatif_projet',
            'sources_financement',
        ];

        // Remplir chaque champ
        foreach ($attributes as $attribut) {
            $valeur = $dataMap[$attribut] ?? '';
            $templateProcessor->setValue($attribut, $valeur);
        }
    }

    /**
     * Formater les bytes en taille lisible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Générer un hash d'accès sécurisé pour le fichier
     */
    private function generateFileAccessHash(string $projetId, string $storageName, string $category): string
    {
        return hash('sha256', $projetId . $storageName . $category . config('app.key'));
    }
}
