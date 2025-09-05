<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API de Gestion des Idées de Projets - GDIZ",
 *      description="Documentation de l'API pour la gestion des idées de projets et l'authentification",
 *      @OA\Contact(
 *          email="support@gdiz.bj"
 *      ),
 *      @OA\License(
 *          name="Proprietary",
 *          url="#"
 *      )
 * )
 * 
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Serveur API"
 * )
 * 
 * @OA\SecurityScheme(
 *      securityScheme="passport",
 *      type="oauth2",
 *      @OA\Flow(
 *          flow="password",
 *          tokenUrl="/api/passport-auths/authentification",
 *          scopes={}
 *      )
 * )
 * 
 * @OA\Tag(
 *     name="Auth - Connexion",
 *     description="Endpoints pour la connexion et la déconnexion des utilisateurs"
 * )
 * 
 * @OA\Tag(
 *     name="Auth - Gestion de compte",
 *     description="Endpoints pour la confirmation, activation et vérification des comptes"
 * )
 * 
 * @OA\Tag(
 *     name="Auth - Mot de passe",
 *     description="Endpoints pour la réinitialisation et modification des mots de passe"
 * )
 * 
 * @OA\Tag(
 *     name="Auth - Sessions",
 *     description="Endpoints pour la gestion des sessions et tokens utilisateurs"
 * )
 * 
 * @OA\Tag(
 *     name="Évaluations - Configuration",
 *     description="Endpoints pour configurer les grilles d'évaluation climatique et multicritères"
 * )
 * 
 * @OA\Tag(
 *     name="Documents - Templates",
 *     description="Endpoints pour gérer les templates de documents dynamiques (fiches d'idées, canevas de notes conceptuelles)"
 * )
 */
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
