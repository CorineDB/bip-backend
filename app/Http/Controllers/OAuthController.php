<?php

namespace App\Http\Controllers;

use App\Http\Requests\auth\ChangePasswordRequest;
use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Services\Contracts\PassportOAuthServiceInterface;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    protected $maxAttempts = 3; // Default is 5
    protected $decayMinutes = 1; // Default is 1

    /**
     * @var service
     */
    private $oauthService;

    /**
     * Instantiate a new AuthController instance.
     * @param PassportOAuthServiceInterface $oauthServiceInterface
     */
    public function __construct(PassportOAuthServiceInterface $oauthServiceInterface)
    {
        //$this->middleware(['auth:oauth'])->only(['deconnexion', 'utilisateurConnecte']);
        $this->oauthService = $oauthServiceInterface;

    }

    /**
     * Authentification et permission d'accès au système
     *
     * @OA\Post(
     *     path="/api/passport-auths/authentification",
     *     tags={"Auth - Connexion"},
     *     summary="Authentification utilisateur",
     *     description="Permet à un utilisateur de se connecter au système avec ses identifiants",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email","password"},
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Adresse email de l'utilisateur"),
     *                 @OA\Property(property="password", type="string", format="password", example="motdepasse123", description="Mot de passe de l'utilisateur")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentification réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=86400),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="name", type="string", example="John Doe")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants incorrects",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Identifiants incorrects")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * @param  App\Http\Requests\auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authentification(LoginRequest $request)
    {
        return $this->oauthService->authentification($request->all());
    }

    /**
     * Récupérer l'information de l'utilisateur connecté
     *
     * @OA\Get(
     *     path="/api/passport-auths/utilisateur-connecte",
     *     tags={"Auth - Sessions"},
     *     summary="Informations utilisateur connecté",
     *     description="Récupère les informations de l'utilisateur actuellement authentifié",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations utilisateur récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Informations utilisateur récupérées"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function utilisateurConnecte(Request $request)
    {
        return $this->oauthService->utilisateurConnecte($request);
    }

    /**
     * Vérification de l'email pour réinitialisation du mot de passe
     *
     * @OA\Get(
     *     path="/api/passport-auths/reinitialisation-de-mot-de-passe/{email}",
     *     tags={"Auth - Mot de passe"},
     *     summary="Demande de réinitialisation de mot de passe",
     *     description="Envoie un email avec un lien de réinitialisation de mot de passe",
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="Adresse email de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", format="email", example="user@example.com")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email de réinitialisation envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email de réinitialisation envoyé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Utilisateur introuvable")
     *         )
     *     )
     * )
     *
     * @param  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationEmailReinitialisationMotDePasse($email)
    {
        return $this->oauthService->verificationEmailReinitialisationMotDePasse($email);
    }

    /**
     * Confirmation du compte utilisateur
     *
     * @OA\Get(
     *     path="/api/passport-auths/confirmation-de-compte/{email}",
     *     tags={"Auth - Gestion de compte"},
     *     summary="Confirmation de compte",
     *     description="Envoie un email de confirmation de compte à l'utilisateur",
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="Adresse email de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", format="email", example="user@example.com")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email de confirmation envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email de confirmation envoyé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Utilisateur introuvable")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmationDeCompte($email)
    {
        return $this->oauthService->confirmationDeCompte($email);
    }

    /**
     * Activation du compte utilisateur
     *
     * @OA\Get(
     *     path="/api/passport-auths/activation-de-compte/{token}",
     *     tags={"Auth - Gestion de compte"},
     *     summary="Activation de compte",
     *     description="Active le compte d'un utilisateur à partir d'un token de confirmation",
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="Token d'activation reçu par email",
     *         required=true,
     *         @OA\Schema(type="string", example="abc123def456ghi789")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte activé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Compte activé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token d'activation invalide ou expiré")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Compte déjà activé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ce compte est déjà activé")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activationDeCompte($token)
    {
        return $this->oauthService->activationDeCompte($token);
    }

    /**
     * Vérification du compte utilisateur
     *
     * @OA\Get(
     *     path="/api/passport-auths/verification-de-compte/{token}",
     *     tags={"Auth - Gestion de compte"},
     *     summary="Vérification de compte",
     *     description="Vérifie la validité d'un token de vérification de compte",
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="Token de vérification",
     *         required=true,
     *         @OA\Schema(type="string", example="xyz789abc123def456")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token valide",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Token de vérification valide"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="valid", type="boolean", example=true),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-12-31T23:59:59Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token de vérification invalide ou expiré")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationDeCompte($token)
    {
        return $this->oauthService->verificationDeCompte($token);
    }

    public function debloquer($id)
    {
        return $this->oauthService->debloquer($id);
    }

    /**
     * Réinitialisation de mot de passe
     *
     * @OA\Post(
     *     path="/api/passport-auths/reinitialisation-de-mot-de-passe",
     *     tags={"Auth - Mot de passe"},
     *     summary="Réinitialisation du mot de passe",
     *     description="Réinitialise le mot de passe d'un utilisateur avec un token de réinitialisation valide",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"token","new_password","new_password_confirmation"},
     *                 @OA\Property(property="token", type="string", example="abc123def456", description="Token de réinitialisation reçu par email"),
     *                 @OA\Property(property="new_password", type="string", format="password", example="NouveauMotDePasse123!", description="Nouveau mot de passe (min 8 caractères, majuscules, minuscules, chiffres, symboles)"),
     *                 @OA\Property(property="new_password_confirmation", type="string", format="password", example="NouveauMotDePasse123!", description="Confirmation du nouveau mot de passe")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide ou expiré")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * @param  App\Http\Requests\auth\ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reinitialisationDeMotDePasse(ResetPasswordRequest $request)
    {
        return $this->oauthService->reinitialisationDeMotDePasse($request->all());
    }

    /**
     * Réinitilisation de mot de passe
     *
     * @param  App\Http\Requests\auth\ChangePasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function modificationDeMotDePasse(ChangePasswordRequest $request)
    {
        return $this->oauthService->reinitialisationDeMotDePasse($request->all());
    }

    /**
     * Déconnecter l'utilisateur authentifié
     *
     * @OA\Post(
     *     path="/api/passport-auths/deconnexion",
     *     tags={"Auth - Connexion"},
     *     summary="Déconnexion utilisateur",
     *     description="Déconnecte l'utilisateur authentifié et révoque son token",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deconnexion(Request $request)
    {
        return $this->oauthService->deconnexion($request);
    }

    /**
     * Actualiser le token
     *
     * @OA\Get(
     *     path="/api/passport-auths/refresh-token",
     *     tags={"Auth - Sessions"},
     *     summary="Actualiser le token d'authentification",
     *     description="Génère un nouveau token d'authentification pour l'utilisateur connecté",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token actualisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Token actualisé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=86400)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh_token(Request $request)
    {
        return $this->oauthService->refresh_token($request);
    }

}
