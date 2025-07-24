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
     * Authentfication et permission d'accès au système
     *
     * @param  App\Http\Requests\auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authentification(LoginRequest $request)
    {
        return $this->oauthService->authentification($request->all());
    }

    /**
     * Récupérer l'information de l'utilisateur connecter
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function utilisateurConnecte(Request $request)
    {
        return $this->oauthService->utilisateurConnecte($request);
    }

    /**
     * Vérification de l'email
     *
     * @param  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationEmailReinitialisationMotDePasse($email)
    {
        return $this->oauthService->verificationEmailReinitialisationMotDePasse($email);
    }

    /**
     * verification du compte
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmationDeCompte($email)
    {
        return $this->oauthService->confirmationDeCompte($email);
    }

    /**
     * confirmation et activation de compte
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activationDeCompte($token)
    {
        return $this->oauthService->activationDeCompte($token);
    }

    /**
     * verification du compte
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
     * Réinitilisation de mot de passe
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
     * @param  App\Http\Requests\auth\ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function modificationDeMotDePasse(ChangePasswordRequest $request)
    {
        return $this->oauthService->reinitialisationDeMotDePasse($request->all());
    }

    /**
     * Déconnecter l'utilisateur authentifié
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh_token(Request $request)
    {
        return $this->oauthService->refresh_token($request);
    }

}
