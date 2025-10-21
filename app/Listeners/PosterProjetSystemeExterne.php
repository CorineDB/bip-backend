<?php

namespace App\Listeners;

use App\Events\RapportEvaluationExAnteValide;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PosterProjetSystemeExterne implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RapportEvaluationExAnteValide $event): void
    {
        $rapport = $event->rapport;
        $projet = $event->projet;
        $evaluation = $event->evaluation;
        $validateur = $event->validateur;
        $decision = $event->decision;

        // Vérifier que la décision est "valider"
        if ($decision !== 'valider') {
            Log::info('Synchronisation système externe ignorée - décision n\'est pas "valider"', [
                'projet_id' => $projet->id,
                'decision' => $decision,
            ]);
            return;
        }

        Log::info('Démarrage synchronisation projet vers système externe', [
            'projet_id' => $projet->id,
            'projet_titre' => $projet->titre_projet,
            'rapport_id' => $rapport->id,
        ]);

        try {
            // Préparer les données du projet pour le système externe
            $dataProjet = $this->preparerDonneesProjet($projet, $rapport, $evaluation);

            // URL du système externe (à configurer dans .env)
            $urlSystemeExterne = config('services.systeme_externe.url');
            $apiToken = config('services.systeme_externe.token');

            if (!$urlSystemeExterne) {
                Log::warning('URL du système externe non configurée', [
                    'projet_id' => $projet->id,
                ]);
                return;
            }

            // Envoyer les données au système externe
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($urlSystemeExterne . '/api/projets', $dataProjet);

            if ($response->successful()) {
                $responseData = $response->json();

                // Enregistrer la référence du système externe dans le projet
                $projet->update([
                    'reference_systeme_externe' => $responseData['id'] ?? null,
                    'date_synchronisation_externe' => now(),
                    'statut_synchronisation_externe' => 'synchronise',
                ]);

                Log::info('Projet synchronisé avec succès vers système externe', [
                    'projet_id' => $projet->id,
                    'reference_externe' => $responseData['id'] ?? null,
                    'response' => $responseData,
                ]);
            } else {
                Log::error('Échec de synchronisation vers système externe', [
                    'projet_id' => $projet->id,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                // Marquer comme échec
                $projet->update([
                    'statut_synchronisation_externe' => 'echec',
                    'derniere_erreur_sync' => $response->body(),
                ]);

                // Relancer l'exception pour retry
                throw new \Exception('Échec HTTP: ' . $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation vers système externe', [
                'projet_id' => $projet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Marquer comme échec
            $projet->update([
                'statut_synchronisation_externe' => 'erreur',
                'derniere_erreur_sync' => $e->getMessage(),
            ]);

            // Relancer pour retry automatique
            throw $e;
        }
    }

    /**
     * Préparer les données du projet pour l'envoi au système externe.
     */
    protected function preparerDonneesProjet($projet, $rapport, $evaluation): array
    {
        return [
            // Informations de base du projet
            'titre_projet' => $projet->titre_projet,
            'code_projet' => $projet->code_projet ?? null,
            'description' => $projet->description ?? null,

            // Informations organisationnelles
            'organisation' => [
                'id' => $projet->organisation_id,
                'nom' => $projet->organisation->nom ?? null,
                'type' => $projet->organisation->type ?? null,
            ],

            'ministere' => [
                'id' => $projet->ministere_id,
                'nom' => $projet->ministere->nom ?? null,
            ],

            // Informations financières
            'budget' => [
                'montant_total' => $projet->montant_total ?? null,
                'devise' => 'XOF', // ou récupérer depuis config
                'investissement_initial' => $projet->investissement_initial ?? null,
            ],

            // Informations temporelles
            'duree_projet' => $projet->duree_vie ?? null,
            'date_debut_prevue' => $projet->date_debut ?? null,
            'date_fin_prevue' => $projet->date_fin ?? null,
            'date_validation' => now()->toDateString(),

            // Statut et phase
            'statut' => $projet->statut->value ?? null,
            'phase' => $projet->phase ?? null,
            'type_projet' => $projet->type_projet?->value ?? null,

            // Localisation
            'localisation' => [
                'region' => $projet->region ?? null,
                'commune' => $projet->commune ?? null,
                'villages' => $projet->villages ?? [],
            ],

            // Secteurs et thématiques
            'secteur' => $projet->secteur->nom ?? null,
            'sous_secteur' => $projet->sous_secteur ?? null,

            // ODD et impacts
            'odds_cibles' => $projet->odds->pluck('code')->toArray() ?? [],
            'cibles' => $projet->cibles->pluck('code')->toArray() ?? [],

            // Informations du rapport d'évaluation ex-ante
            'rapport_evaluation' => [
                'id' => $rapport->id,
                'date_soumission' => $rapport->date_soumission?->toDateString(),
                'date_validation' => $rapport->date_validation?->toDateString(),
                'evaluateur_id' => $evaluation->evaluateur_id,
                'validateur_id' => $evaluation->valider_par,
            ],

            // Indicateurs de performance (si disponibles)
            'van' => $projet->van ?? null,
            'tri' => $projet->tri ?? null,

            // Métadonnées
            'metadata' => [
                'source' => 'GDIZ',
                'version_api' => '1.0',
                'date_export' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(RapportEvaluationExAnteValide $event, \Throwable $exception): void
    {
        Log::error('Échec définitif de synchronisation vers système externe', [
            'projet_id' => $event->projet->id,
            'tentatives' => $this->tries,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Marquer comme échec définitif
        $event->projet->update([
            'statut_synchronisation_externe' => 'echec_definitif',
            'derniere_erreur_sync' => 'Échec après ' . $this->tries . ' tentatives: ' . $exception->getMessage(),
        ]);

        // Vous pouvez aussi envoyer une notification aux administrateurs ici
        // Notification::send($admins, new SynchronisationExterneEchouee($event->projet));
    }
}
