<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class UnhashRouteParameters
{
    /**
     * Liste des paramètres de route qui doivent être déhashés
     * Format: 'parameter_name' => 'ModelClass'
     */
    protected array $hashableParameters = [
        'id' => null, // null = déhasher sans vérification de modèle
        'arrondissement' => \App\Models\Arrondissement::class,
        'arrondissementId' => \App\Models\Arrondissement::class,
        'village' => \App\Models\Village::class,
        'villageId' => \App\Models\Village::class,
        'commune' => \App\Models\Commune::class,
        'communeId' => \App\Models\Commune::class,
        'ideeProjetId' => \App\Models\IdeeProjet::class,
        'idee_projet' => \App\Models\IdeeProjet::class,
        'projetId' => \App\Models\Projet::class,
        'projet' => \App\Models\Projet::class,
        'categorie_critere' => \App\Models\CategorieCritere::class,
        'categorie_document' => \App\Models\CategorieDocument::class,
        'categorie_projet' => \App\Models\CategorieProjet::class,
        'cible' => \App\Models\Cible::class,
        'odd' => \App\Models\Odd::class,
        'commentaire' => \App\Models\Commentaire::class,
        'fichier' => \App\Models\Fichier::class,
        'composant_programme' => \App\Models\ComposantProgramme::class,
        'programme' => \App\Models\TypeProgramme::class,
        'idComposantProgramme' => \App\Models\ComposantProgramme::class,
        'idProgramme' => \App\Models\TypeProgramme::class,
        'document' => \App\Models\Document::class,
        'secteur' => \App\Models\Secteur::class,
        'financement' => \App\Models\Financement::class,
        'idNature' => \App\Models\Financement::class,
        'idType' => \App\Models\Financement::class,
        'noteId' => \App\Models\NoteConceptuelle::class,
        'evaluationId' => \App\Models\Evaluation::class,
        'organisation' => \App\Models\Organisation::class,
        'organisationId' => \App\Models\Organisation::class,
        'ministereId' => \App\Models\Organisation::class,
        'permission' => \App\Models\Permission::class,
        'role' => \App\Models\Role::class,
        'userId' => \App\Models\User::class,
        'user' => \App\Models\User::class,
        'personne' => \App\Models\Personne::class,
        'evaluation' => \App\Models\Evaluation::class,
        'type_intervention' => \App\Models\TypeIntervention::class,
        'type_programme' => \App\Models\TypeProgramme::class,
        'dpaf' => \App\Models\Dpaf::class,
        'dgpd' => \App\Models\Dgpd::class,
        'groupe_utilisateur' => \App\Models\GroupeUtilisateur::class,
        'user_id' => \App\Models\User::class,
        'fichier_id' => \App\Models\Fichier::class,
        'dossier_id' => \App\Models\Dossier::class,
        'projet_id' => \App\Models\Projet::class,
        // Ajouter d'autres paramètres selon les besoins
    ];

    /**
     * Gérer une requête entrante
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        if (!$route) {
            return $next($request);
        }

        $parameters = $route->parameters();

        foreach ($parameters as $key => $value) {

            // Vérifier si ce paramètre doit être déhashé
            if ($this->shouldUnhash($key) && is_string($value)) {
                $unhashedValue = $this->unhashParameter($key, $value);

                if ($unhashedValue !== null) {
                    // Remplacer le paramètre hashé par sa valeur déhashée
                    $route->setParameter($key, $unhashedValue);
                } else {
                    Log::warning('Impossible de déhasher le paramètre de route', [
                        'parameter' => $key,
                        'value' => $value,
                        'route' => $request->path()
                    ]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Vérifier si un paramètre doit être déhashé
     */
    protected function shouldUnhash(string $parameter): bool
    {
        return array_key_exists($parameter, $this->hashableParameters);
    }

    /**
     * Déhasher un paramètre
     */
    protected function unhashParameter(string $parameter, string $value)
    {
        $modelClass = $this->hashableParameters[$parameter];

        if ($modelClass && method_exists($modelClass, 'unhashId')) {
            // Utiliser la méthode du modèle si disponible
            return $modelClass::unhashId($value);
        }

        // Fallback: utiliser Hashids directement
        try {
            $hashids = new \Hashids\Hashids(
                config('app.hashids_salt', config('app.key')),
                config('app.hashids_min_length', 64)
            );
            $decoded = $hashids->decode($value);
            return !empty($decoded) ? $decoded[0] : null;
        } catch (\Exception $e) {
            Log::error('Erreur lors du déhashage du paramètre de route', [
                'parameter' => $parameter,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Ajouter dynamiquement un paramètre hashable
     */
    public function addHashableParameter(string $parameter, ?string $modelClass = null): void
    {
        $this->hashableParameters[$parameter] = $modelClass;
    }
}
