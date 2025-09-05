<?php

namespace App\Services\Traits;

use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait ExceptionTrait
{
    use ResponseJsonTrait;

    public function apiExceptions($request,$e)
    {

        if($this->isQuery($e)){
            return $this->QueryResponse($e);
        }

        if($this->isModel($e)){
            return $this->ModelResponse($e);
        }

        if ($this->notAllowed($e)) {
            return $this->NotAllowedResponse($e);
        }

        if($this->isHttp($e)){
            return $this->HttpResponse($e);
        }

        if($this->isAuthentication($e)){
            return $this->AuthenticationResponse($e);
        }

        if($this->isUnauthorized($e)){
            return $this->UnauthorizedResponse($e);
        }

        if($this->isValidation($e,$request)){
            return $this->ValidationResponse($e,$request);
        }

        if($this->isTokenMismatch($e)){
            return $this->TokenMismatchResponse($e);
        }

        if($this->isError($e)){
            return $this->ErrorsResponse($e);
        }
        else{
            return $this->ErrorsResponse($e);
        }
    }

    protected function isError($e){
        return $e instanceof ErrorException;
    }

    protected function isModel($e){
        return $e instanceof ModelNotFoundException;
    }

    protected function isQuery($e){
        return $e instanceof QueryException;
    }

    protected function isHttp($e){
        return $e instanceof NotFoundHttpException;
    }

    protected function isAuthentication($e){
        return $e instanceof AuthenticationException;
    }

    protected function isUnauthorized($e){
        return $e instanceof UnauthorizedHttpException;
    }

    protected function isValidation($e, $request = null){
        return $e instanceof ValidationException;
    }

    protected function isTokenMismatch($e){
        return $e instanceof TokenMismatchException;
    }

    protected function notAllowed($e)
    {
        return $e instanceof MethodNotAllowedHttpException;
    }

    protected function ModelResponse($e){
        // Extraire le nom du modèle depuis l'exception
        $model = class_basename($e->getModel());
        $modelNames = [
            'Projet' => 'Projet',
            'User' => 'Utilisateur',
            'IdeeProjet' => 'Idée de projet',
            'Tdr' => 'Terme de reference',
            'Evaluation' => 'Évaluation',
            'Document' => 'Canevas',
            'Fichier' => 'Fichier',
            'Champ' => 'Champ de formulaire',
            'Decision' => 'Décision',
            'Workflow' => 'Workflow',
            'Notification' => 'Notification',
            'Permission' => 'Permission',
            'Role' => 'Rôle',
            'GroupeUtilisateur' => 'Groupe d\'utilisateur',
            'Ministere' => 'Ministère',
            'Direction' => 'Direction',
            'Service' => 'Service',
            'CategorieCritere' => 'Outil d\'évaluation',
            'Critere' => 'Critère d\'évaluation',
            'NoteConceptuelle' => 'Note conceptuelle',
            'Rapport' => 'Rapport',
            'Commentaire' => 'Commentaire',
            'Notation' => 'Notation',
            'EvaluationCritere' => 'Évaluation de critère',
            'Personne' => 'Personne',
            'Organisation' => 'Organisation',
            'CategorieDocument' => 'Catégorie de document',
            'CategorieProjet' => 'Catégorie de projet',
            'Statut' => 'Statut',
            'TrackInfo' => 'Information de suivi',
            'Commune' => 'Commune',
            'Arrondissement' => 'Arrondissement',
            'Departement' => 'Département',
            'Village' => 'Village',
            'LieuIntervention' => 'Lieu d\'intervention',
            'Financement' => 'Financement',
            'TypeIntervention' => 'Type d\'intervention',
            'TypeProgramme' => 'Type de programme',
            'ComposantProgramme' => 'Composant de programme',
            'Secteur' => 'Secteur',
            'Cible' => 'Cible',
            'ChampSection' => 'Section de formulaire',
            'ChampProjet' => 'Champ de formulaire',
            'Dgpd' => 'DGPD',
            'Dpaf' => 'DPAF',
            'Odd' => 'Objectif de developpement durable'
        ];
        $resourceName = $modelNames[$model] ?? 'Ressource';
        return $this->errorResponse($resourceName . ' non trouvé(e)', [], Response::HTTP_NOT_FOUND);
    }

    protected function QueryResponse($e){
        return $this->errorResponse($e->getMessage(), [], Response::HTTP_NOT_FOUND);
    }

    protected function NotAllowedResponse($e)
    {
        return $this->errorResponse("Méthode HTTP non autorisée pour cette route", [], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    protected function ErrorsResponse($e)
    {
        return $this->errorResponse($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function HttpResponse($e)
    {
        return $this->errorResponse($e->getMessage() ?? " Route inconnue. Veuillez revérifier la route de votre requête", [], Response::HTTP_NOT_FOUND);
    }

    protected function AuthenticationResponse($e)
    {
        return $this->errorResponse("Connexion expiré. Veuillez vous connecter", [], Response::HTTP_UNAUTHORIZED);
    }

    protected function UnauthorizedResponse($e)
    {
        return $this->errorResponse("Vous n'avez pas le droit d'effectuer cette action", [], Response::HTTP_FORBIDDEN);
    }

    protected function ValidationResponse($e,$request)
    {
        return $this->errorResponse("Erreur de validation du formulaire", $e->validator->errors()->getMessages(), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function TokenMismatchResponse($e){
        return $this->errorResponse("Votre session a expiré, veuillez vous reconnecté", [], 419);
    }
}
