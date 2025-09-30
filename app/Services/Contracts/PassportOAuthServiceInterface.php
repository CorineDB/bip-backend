<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface PassportOAuthServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here

    /**
     * Vérification de compte et permission d'accéder au système grâce au token
     *
     * @param array $identifiants
     * @return Illuminate\Http\JsonResponse
     */
    public function authentification($identifiants): JsonResponse;

    /**
     * Récupérer les détails de l'utilisateur connecté.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\JsonResponse
     */
    public function utilisateurConnecte(Request $request): JsonResponse;

    /**
     * Déconnecter l'utilisateur qui est authentifié et connecter au système.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\JsonResponse
     */
    public function deconnexion(Request $request): JsonResponse;

    /**
     * Activation de compte utilisateur
     *
     * @param string $email
     * @return Illuminate\Http\JsonResponse
     */
    public function activationDeCompte($token): JsonResponse;

    /**
     * Activation de compte utilisateur
     *
     * @param string $email
     * @return Illuminate\Http\JsonResponse
     */
    public function confirmationDeCompte($email): JsonResponse;

    /**
     * Verification de compte utilisateur
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function verificationDeCompte($email): JsonResponse;

    /**
     * Réinitialisation du mot de passe de l'utilisateur
     *
     * @param String $token
     * @param array $attributes
     * @return Illuminate\Http\JsonResponse
     */
    public function reinitialisationDeMotDePasse(array $attributes): JsonResponse;

    /**
     * Vérification du mail
     * @param $email
     * @return Illuminate\Http\JsonResponse
     */
    public function verificationEmailReinitialisationMotDePasse($email): JsonResponse;

    /**
     * Handle the SSO provider callback
     * @param string $code
     * @param string $state
     * @return Illuminate\Http\JsonResponse
     */
    //public function handleProviderCallback(string $code, string $state): JsonResponse;
}
