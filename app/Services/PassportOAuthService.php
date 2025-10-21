<?php

namespace App\Services;

use App\Http\Resources\auth\AuthResource;
use App\Http\Resources\auth\LoginResource;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\OAuth2Resource;
use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\PassportOAuthServiceInterface;
use App\Services\Traits\ConfigueTrait;
use App\Services\Traits\IdTrait;
use App\Services\Traits\TooManyFailedAttemptsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use App\Http\Resources\PassportClientResource;

class PassportOAuthService extends BaseService implements PassportOAuthServiceInterface
{
    use TooManyFailedAttemptsTrait, ConfigueTrait, IdTrait;

    protected UserRepositoryInterface $userRepository;
    protected ClientRepository $clientRepository;

    /**
     * AuthService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository, ClientRepository $clientRepository)
    {
        parent::__construct($userRepository);
        $this->clientRepository = $clientRepository;
    }

    protected function getResourceClass(): string
    {
        return OAuth2Resource::class;
    }

    /**
     * Vérification de compte et permission d'accéder au système grâce au token
     *
     * @param array $identifiants
     * @return Illuminate\Http\JsonResponse
     */
    public function authentification($identifiants): JsonResponse
    {
        $this->checkTooManyFailedAttempts();

        try {

            // Si la variable utilisateur est null alors une exception sera déclenché notifiant que l'email renseigner ne correspond à aucun enregistrement de la table users
            if (!($utilisateur = $this->repository->findByAttribute('email', $identifiants['email']))) {

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Identifiant incorrect", 401);
            }

            // Vérifier si le mot de passe renseigner correspond au mot de passe du compte uitisateur trouver
            if (!Hash::check($identifiants['password'], $utilisateur->password)) {

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Mot de passe incorrect", 401);
            }

            // Vérifier si le compte de l'utilisateur est activé ou pas
            /*if (!$utilisateur->email_verified_at)
            {
                throw new Exception("Veuillez confimer votre compte", 403);
            }

            if ($utilisateur->statut !== "actif")
            {
                if ($utilisateur->lastRequest == null)
                {
                    throw new Exception("Veuillez réinitialiser votre mot de passe", 403);
                }
                else if ($utilisateur->statut === "suspendu"){
                    throw new Exception("Votre compte à été bloquer temporairement. Veuillez contacté votre administrateur. ", 403);
                }
                else{
                    throw new Exception("Votre compte n'est pas activé. Veuillez activer votre compte. ", 403);
                }
            }

            if($utilisateur->lastRequest)
            {
                if((strtotime(date('Y-m-d h:i:s')) - strtotime($utilisateur->lastRequest))/3600 >= 4)
                {
                    $utilisateur->tokens()->delete();
                }
            }

            if($utilisateur->tokens()->count()){
                throw new Exception("Une session est déjà active pour ce compte. Veuillez vous déconnectez de tous les autres appareils." . json_encode($utilisateur->tokens), 500);
            }*/

            $token = null;

            if (Auth::attempt(['email' => $identifiants["email"], 'password' => $identifiants["password"]])) {

                $utilisateur = Auth::user();

                // Creating a token without scopes...
                $token = $utilisateur->createToken('Bip-Token')->toArray();

                //$data = $this->createTokenCredentials($identifiants);

                $utilisateur->lastRequest = date('Y-m-d H:i:s');
                $utilisateur->save();

            } else {

                return response()->json([
                    'success' => true,
                    'statusCode' => 401,
                    'message' => 'Unauthorized.',
                    'errors' => 'Unauthorized',
                ], 401);
            }

            RateLimiter::clear($this->throttleKey());

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " s'est connecté.";

            //LogActivity::addToLog("Connexion", $message, get_class($user), $user->id);

            // Retourner le token
            return response()->json(['statut' => 'success', 'message' => 'Authentification réussi', 'data' => new LoginResource($token), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK)/*->withCookie('XSRF-TOKEN', $data['access_token'], 60*3)*/;
        } catch (\Throwable $th) {

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification de compte et permission d'accéder au système grâce au token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\JsonResponse
     */
    public function utilisateurConnecte(Request $request): JsonResponse
    {
        try {
            // retourner les informations de l'utilisateur connecté c'est à dire l'utilisateur qui envoie la requête
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new AuthResource($request->user()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Déconnecter l'utilisateur qui est authentifié et connecter au système.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\JsonResponse
     */
    public function deconnexion(Request $request): JsonResponse
    {
        try {

            $user = Auth::user();

            $acteur = $user ? $user->nom . " " . $user->prenom : "Inconnu";

            // Si la suppression du token ne se passe pas correctement, une exception sera déclenchée
            if (!$request->user()->token()->delete()) throw new Exception("Erreur pendant la déconnexion", 500);

            $message = Str::ucfirst($acteur) . " vient de se déconnecter.";

            //LogActivity::addToLog("Connexion", $message, get_class($user), $user->id);

            return response()->json(['statut' => 'success', 'message' => 'Vous êtes déconnecté', 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function refresh_token(Request $request)
    {
        try {

            $user = $request->user();

            // Si la suppression du token ne se passe pas correctement, une exception sera déclenchée
            if (!$user->token()->delete()) throw new Exception("Erreur pendant le processus de rafraichissement du token", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ["access_token" => $user->createToken($this->hashID(8))->plainTextToken], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Activation de compte utilisateur
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function activationDeCompte($token): JsonResponse
    {

        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if (($utilisateur = $this->repository->findByAttribute('token', $token)) === null) {
                throw new Exception("Veuillez soumettre une demande d'activation de compte", 1);
            }

            if ($utilisateur->account_verification_request_sent_at === null) throw new Exception("Veuillez soumettre une demande d'activation de votre compte", 1);

            if (!$utilisateur->link_is_valide)  throw new Exception("Lien d'activation de votre compte expiré. Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if (Carbon::parse($utilisateur->account_verification_request_sent_at)->addMinutes($this->dureeValiditerLien)->lte(Carbon::now())) throw new Exception("Le lien de vérification de compte n'est plus valide. Veuillez soumettre une nouvelle demande .", 401);

            if ($utilisateur->email_verified_at === null) {
                // Enrégistrement de la date et l'heure de vérification du compte
                $utilisateur->email_verified_at = now();
            } elseif ($utilisateur->statut === 0) {
                $utilisateur->statut = 1;
            } else {
                throw new Exception("Erreur d'activation du compte", 500);
            }

            $utilisateur->account_verification_request_sent_at = null;

            $utilisateur->link_is_valide = false;

            $utilisateur->token = null;

            // Sauvegarder les informations
            $utilisateur->save();

            DB::commit();

            $acteur = $utilisateur ? $utilisateur->nom . " " . $utilisateur->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " vient d'activer son compte.";

            //LogActivity::addToLog("Connexion", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Compte utilisateur activé', 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            if ($utilisateur) {

                $utilisateur->account_verification_request_sent_at = null;

                $utilisateur->link_is_valide = false;

                $utilisateur->token = null;

                // Sauvegarder les informations
                $utilisateur->save();
            }

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification d'email de réinitialisation de mot de passe
     *
     * @param array $email
     * @return Illuminate\Http\JsonResponse
     */
    public function confirmationDeCompte($email): JsonResponse
    {
        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            $utilisateur = User::where("email", $email)->first();

            // Si l'utilisateur n'existe pas envoyé une reponse avec comme status code 404
            if (!$utilisateur) throw new Exception("Utilisateur inconnu", 500);

            if ($utilisateur->statut === 1) throw new Exception("Votre compte est déjà activé", 1);

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make($utilisateur->id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();

            DB::commit();

            //Send verificiation email
            dispatch(new SendEmailJob($utilisateur, "confirmation-de-compte"))->delay(now()->addSeconds(15));

            // retourner une reponse avec les détails de l'utilisateur
            return response()->json(['statut' => 'success', 'message' => "E-Mail de d'activation de compte envoyé", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verification de compte utilisateur
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function verificationDeCompte($token): JsonResponse
    {

        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if (($utilisateur = $this->repository->findByAttribute('token', $token)) === null) {
                throw new Exception("Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);
            }

            if ($utilisateur->account_verification_request_sent_at === null) throw new Exception("Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if (!$utilisateur->link_is_valide) throw new Exception("Lien de réinitialisation de votre mot de passe n'est plus valide. Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if (Carbon::parse($utilisateur->account_verification_request_sent_at)->addMinutes($this->dureeValiditerLien)->lte(Carbon::now())) throw new Exception("Le lien de vérification de compte a expiré. Veuillez soumettre une nouvelle demande.", 401);

            $utilisateur->link_is_valide = false;

            // Sauvegarder les informations
            $utilisateur->save();

            DB::commit();

            $acteur = $utilisateur ? $utilisateur->nom . " " . $utilisateur->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " vient de confirmer son compte pour la réinitialisation de mot de passe.";

            //LogActivity::addToLog("Confirmation de compte", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Compte identifier', 'data' => [
                'email' => $utilisateur->email
            ], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function debloquer($id): JsonResponse
    {
        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if (($utilisateur = $this->repository->findByKey($id)) === null) {
                throw new Exception("Utilisateur introuvalbe", 1);
            }

            $utilisateur->statut = 1;

            // Sauvegarder les informations
            $utilisateur->save();

            DB::commit();

            $acteur = Auth::user()->nom;

            $message = Str::ucfirst($acteur) . " vient de debloquer " . $utilisateur->nom;

            //LogActivity::addToLog("Deblocage de compte", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Compte debloquer', 'data' => [
                'email' => $utilisateur->email
            ], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification d'email de réinitialisation de mot de passe
     *
     * @param string $email
     * @return Illuminate\Http\JsonResponse
     */
    public function verificationEmailReinitialisationMotDePasse($email): JsonResponse
    {
        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            $utilisateur = $this->repository->findByAttribute('email', $email);

            // Si l'utilisateur n'existe pas envoyé une reponse avec comme status code 404
            if (!$utilisateur) throw new Exception("Utilisateur inconnu", 500);

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make($utilisateur->id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();

            DB::commit();

            //Send verificiation email
            dispatch(new SendEmailJob($utilisateur, "reinitialisation-mot-de-passe"))->delay(now()->addSeconds(15));

            // retourner une reponse avec les détails de l'utilisateur
            return response()->json(['statut' => 'success', 'message' => "E-Mail de réinitialisation de mot de passe envoyé", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Réinitialisation du mot de passe de l'utilisateur
     *
     * @param String $token
     * @param array $attributes
     * @return Illuminate\Http\JsonResponse
     */
    public function reinitialisationDeMotDePasse(array $attributes): JsonResponse
    {

        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if (!($utilisateur = $this->repository->findByAttribute('token', $attributes['token']))) {
                throw new Exception("Utilisateur inconnu", 500);
            }
            /*elseif(!($utilisateur = $utilisateur->where('email', $attributes['email'])->first()))
            {
                throw new Exception("Utilisateur inconnu", 500);
            }*/

            if ($utilisateur->account_verification_request_sent_at === null) throw new Exception("Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if (Carbon::parse($utilisateur->account_verification_request_sent_at)->addMinutes($this->dureeValiditerLien)->lte(Carbon::now())) throw new Exception("Le delai de validité de votre token est dépassé. Veuillez soumettre une nouvelle demande .", 403);

            // S'assurer que le nouveau mot de passe est différent du mot de passe actuel
            if ((Hash::check($attributes['new_password'], $utilisateur->password))) throw new Exception("Le nouveau mot de passe doit être différent de l'actuel mot de passe. Veuillez vérifier", 422);

            if ((Hash::check($attributes['new_password'], $utilisateur->last_password_remember))) throw new Exception("Le mot de passe doit être différent de vos anciens mot de passe. Veuillez changer", 422);

            /*Password::where("userId", $utilisateur->id)->get()->map(function($item) use ($attributes){

                if( (Hash::check( $attributes['new_password'], $item->password)) )
                {
                    throw new Exception("Le mot de passe doit être différent de vos anciens mot de passe. Veuillez changer", 422);
                }

            });

            Password::create(["password" => $utilisateur->password, "userId" => $utilisateur->id]);*/

            $utilisateur->last_password_remember = $utilisateur->password;

            // Enrégistrer la donnée
            $utilisateur->password =  Hash::make($attributes['new_password']);

            $utilisateur->password_update_at = now();

            if ($utilisateur->email_verified_at === null) {
                // Enrégistrement de la date et l'heure de vérification du compte
                $utilisateur->email_verified_at = now();

                $utilisateur->statut = 1;

                $utilisateur->last_connection = now();
            } elseif ($utilisateur->statut === 0) {
                $utilisateur->statut = 1;

                if ($utilisateur->first_connexion === null) $utilisateur->first_connexion = now();
            } else;

            $utilisateur->account_verification_request_sent_at = null;

            $utilisateur->token = null;

            // Sauvegarder les informations
            $utilisateur->save();

            $utilisateur->tokens()->delete();

            DB::commit();

            $acteur = $utilisateur ? $utilisateur->nom . " " . $utilisateur->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " vient de réinitiliser son mot de passe.";

            //LogActivity::addToLog("Connexion", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Mot de passe réinitialisé', 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate authenticate token
     *
     * @param array $request
     * @param User $id
     * @return mixed
     */
    public function createTokenCredentials(array $credentials)
    {
        try {

            /* $authenticate = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => config('passport.grant_access_client.id'),
                'client_secret' =>  config('passport.grant_access_client.secret'),
                'username' => $request['email'],
                'password' => $request['password'],
                'scope' => '*',
                'type_identifiant' => $request['type_identifiant'], // custom field to pass to findForPassport
            ]); */

            /*$response = Http::asForm()->post('/oauth/token', [
                'grant_type' => 'password',
                'client_id' => config('passport.grant_access_client.id'),
                'client_secret' => config('passport.grant_access_client.secret'),
                'username' => $credentials['email'],
                'password' => $credentials['password'],
                'scope' => 'user:read orders:create',
            ]);*/

            $authenticate = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => config('passport.grant_access_client.id'),
                'client_secret' => config('passport.grant_access_client.secret'),
                'username' => $credentials["email"],
                'password' => $credentials["password"],
                'scope' => '*', // custom field to pass to findForPassport
            ]);

            $response = app()->handle($authenticate)->getContent(); // authenticated user token access

            return json_decode($response);
        } catch (\Throwable $th) {

            $message = $th->getMessage();

            throw new \Exception($message, 500);
        }
    }

    // =============================================================================
    // GESTION DES CLIENTS OAUTH
    // =============================================================================

    /**
     * Liste tous les clients avec pagination et filtres
     */
    public function getClients(array $filters = [], int $perPage = 15): JsonResponse
    {
        try {
            $query = Client::query();

            // Appliquer les filtres
            if (isset($filters['personal_access_client'])) {
                $query->where('personal_access_client', $filters['personal_access_client']);
            }

            if (isset($filters['password_client'])) {
                $query->where('password_client', $filters['password_client']);
            }

            if (isset($filters['revoked'])) {
                $query->where('revoked', $filters['revoked']);
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $clients->getCollection()->transform(function ($client) {
                return new PassportClientResource($client);
            });

            return response()->json([
                'statut' => 'success',
                'message' => 'Liste des clients OAuth récupérée avec succès',
                'data' => $clients,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère un client par son ID
     */
    public function getClient(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            return response()->json([
                'statut' => 'success',
                'message' => 'Client OAuth récupéré avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Crée un nouveau client OAuth
     */
    public function createClient(array $data): JsonResponse
    {
        try {
            $personalAccessClient = $data['personal_access_client'] ?? false;
            $passwordClient = $data['password_client'] ?? false;
            $confidential = $data['confidential'] ?? true;

            $client = new Client();
            $client->name = $data['name'];
            $client->redirect_uris = $data['redirect_uris'] ?? [];
            $client->revoked = false;

            if ($personalAccessClient) {
                $client->secret = null;
                $client->grant_types = ['personal_access'];
            } elseif ($passwordClient) {
                $client->secret = Str::random(40);
                $client->grant_types = ['password'];
            } else {
                $client->secret = $confidential ? Str::random(40) : null;
                $client->grant_types = ['authorization_code'];
                if ($confidential) {
                    $client->grant_types = ['authorization_code', 'client_credentials'];
                }
            }

            $client->save();

            return response()->json([
                'statut' => 'success',
                'message' => 'Client OAuth créé avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 201
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Crée un client credentials (client_credentials grant)
     */
    public function createClientCredentials(array $data): JsonResponse
    {
        try {
            $client = new Client();
            $client->name = $data['name'];
            $client->secret = Str::random(40);
            $client->redirect_uris = $data['redirect_uris'] ?? [];
            $client->grant_types = ['client_credentials'];
            $client->revoked = false;
            $client->save();

            return response()->json([
                'statut' => 'success',
                'message' => 'Client credentials créé avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 201
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Crée un client d'accès personnel (personal access token)
     */
    public function createPersonalAccessClient(array $data): JsonResponse
    {
        try {
            $client = new Client();
            $client->name = $data['name'];
            $client->secret = null; // Pas de secret pour personal access
            $client->redirect_uris = [];
            $client->grant_types = ['personal_access'];
            $client->revoked = false;
            $client->save();

            return response()->json([
                'statut' => 'success',
                'message' => 'Client d\'accès personnel créé avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 201
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Crée un client password grant
     */
    public function createPasswordClient(array $data): JsonResponse
    {
        try {
            $client = new Client();
            $client->name = $data['name'];
            $client->secret = Str::random(40);
            $client->redirect_uris = [];
            $client->grant_types = ['password'];
            $client->revoked = false;
            $client->save();

            return response()->json([
                'statut' => 'success',
                'message' => 'Client password grant créé avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 201
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Crée un client authorization code (avec redirect)
     */
    public function createAuthorizationCodeClient(array $data): JsonResponse
    {
        try {
            $client = new Client();
            $client->name = $data['name'];
            $client->secret = ($data['confidential'] ?? true) ? Str::random(40) : null;
            $client->redirect_uris = $data['redirect_uris'] ?? [];
            $client->grant_types = ['authorization_code'];
            $client->revoked = false;
            $client->save();

            return response()->json([
                'statut' => 'success',
                'message' => 'Client authorization code créé avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 201
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère tous les clients credentials
     */
    public function getClientCredentials(array $filters = [], int $perPage = 15): JsonResponse
    {
        try {
            $query = Client::whereJsonContains('grant_types', 'client_credentials');

            // Appliquer les filtres
            if (isset($filters['revoked'])) {
                $query->where('revoked', $filters['revoked']);
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $clients->getCollection()->transform(function ($client) {
                return new PassportClientResource($client);
            });

            return response()->json([
                'statut' => 'success',
                'message' => 'Liste des clients credentials récupérée avec succès',
                'data' => $clients,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère tous les clients d'accès personnel
     */
    public function getPersonalAccessClients(array $filters = [], int $perPage = 15): JsonResponse
    {
        try {
            $query = Client::where(function($q) {
                $q->whereJsonContains('grant_types', 'personal_access')
                  ->orWhereJsonContains('grant_types', 'personal_access_token');
            });

            // Appliquer les filtres
            if (isset($filters['revoked'])) {
                $query->where('revoked', $filters['revoked']);
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $clients->getCollection()->transform(function ($client) {
                return new PassportClientResource($client);
            });

            return response()->json([
                'statut' => 'success',
                'message' => 'Liste des clients d\'accès personnel récupérée avec succès',
                'data' => $clients,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère tous les clients password grant
     */
    public function getPasswordClients(array $filters = [], int $perPage = 15): JsonResponse
    {
        try {
            $query = Client::whereJsonContains('grant_types', 'password');

            // Appliquer les filtres
            if (isset($filters['revoked'])) {
                $query->where('revoked', $filters['revoked']);
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $clients->getCollection()->transform(function ($client) {
                return new PassportClientResource($client);
            });

            return response()->json([
                'statut' => 'success',
                'message' => 'Liste des clients password grant récupérée avec succès',
                'data' => $clients,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère tous les clients authorization code
     */
    public function getAuthorizationCodeClients(array $filters = [], int $perPage = 15): JsonResponse
    {
        try {
            $query = Client::whereJsonContains('grant_types', 'authorization_code');

            // Appliquer les filtres
            if (isset($filters['revoked'])) {
                $query->where('revoked', $filters['revoked']);
            }

            if (isset($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $clients->getCollection()->transform(function ($client) {
                return new PassportClientResource($client);
            });

            return response()->json([
                'statut' => 'success',
                'message' => 'Liste des clients authorization code récupérée avec succès',
                'data' => $clients,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Met à jour un client credentials
     */
    public function updateClientCredentials(string $clientId, array $data): JsonResponse
    {
        try {
            $client = Client::where('id', $clientId)
                          ->whereJsonContains('grant_types', 'client_credentials')
                          ->first();

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client credentials non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $client->update($data);

            return response()->json([
                'statut' => 'success',
                'message' => 'Client credentials mis à jour avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Met à jour un client d'accès personnel
     */
    public function updatePersonalAccessClient(string $clientId, array $data): JsonResponse
    {
        try {
            $client = Client::where('id', $clientId)
                          ->where(function($q) {
                              $q->whereJsonContains('grant_types', 'personal_access')
                                ->orWhereJsonContains('grant_types', 'personal_access_token');
                          })
                          ->first();

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client d\'accès personnel non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $client->update($data);

            return response()->json([
                'statut' => 'success',
                'message' => 'Client d\'accès personnel mis à jour avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Met à jour un client password grant
     */
    public function updatePasswordClient(string $clientId, array $data): JsonResponse
    {
        try {
            $client = Client::where('id', $clientId)
                          ->whereJsonContains('grant_types', 'password')
                          ->first();

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client password grant non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $client->update($data);

            return response()->json([
                'statut' => 'success',
                'message' => 'Client password grant mis à jour avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Met à jour un client authorization code
     */
    public function updateAuthorizationCodeClient(string $clientId, array $data): JsonResponse
    {
        try {
            $client = Client::where('id', $clientId)
                          ->whereJsonContains('grant_types', 'authorization_code')
                          ->first();

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client authorization code non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $client->update($data);

            return response()->json([
                'statut' => 'success',
                'message' => 'Client authorization code mis à jour avec succès',
                'data' => new PassportClientResource($client),
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Met à jour un client existant
     */
    public function updateClient(string $clientId, array $data): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $client->update($data);
            $clientResource = new PassportClientResource($client->fresh());

            return response()->json([
                'statut' => 'success',
                'message' => 'Client OAuth mis à jour avec succès',
                'data' => $clientResource,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Révoque un client (soft delete)
     */
    public function revokeClient(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $client->update(['revoked' => true]);

            return response()->json([
                'statut' => 'success',
                'message' => 'Client OAuth révoqué avec succès',
                'data' => null,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Restaure un client révoqué
     */
    public function restoreClient(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $client->update(['revoked' => false]);
            $clientResource = new PassportClientResource($client->fresh());

            return response()->json([
                'statut' => 'success',
                'message' => 'Client OAuth restauré avec succès',
                'data' => $clientResource,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Supprime définitivement un client
     */
    public function deleteClient(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            // Révoquer tous les tokens associés
            $client->tokens()->delete();
            $client->delete();

            return response()->json([
                'statut' => 'success',
                'message' => 'Client OAuth supprimé définitivement',
                'data' => null,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Régénère le secret d'un client
     */
    public function regenerateClientSecret(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            // Un client est confidentiel s'il a un secret
            if (empty($client->secret)) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Impossible de régénérer le secret pour ce client non confidentiel',
                    'errors' => [],
                    'statutCode' => 400
                ], 400);
            }

            $newSecret = Str::random(40);
            $client->update(['secret' => $newSecret]);
            $clientResource = new PassportClientResource($client->fresh());

            return response()->json([
                'statut' => 'success',
                'message' => 'Secret du client régénéré avec succès',
                'data' => [
                    'client' => $clientResource,
                    'new_secret' => $newSecret
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère les scopes disponibles
     */
    public function getAvailableScopes(): JsonResponse
    {
        try {
            $scopes = config('passport.scopes', [
                'sync-sigfp' => 'Allow SIGFP to sync data with BIP',
                'integration-bip' => 'Allow Integration with BIP',
                'read-projects' => 'Read project data',
                'manage-projects' => 'Create, update, delete projects',
                'manage-clients' => 'Manage OAuth clients',
                'admin' => 'Full administrative access',
            ]);

            return response()->json([
                'statut' => 'success',
                'message' => 'Scopes disponibles récupérés avec succès',
                'data' => $scopes,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Vérifie si un client existe et est actif
     */
    public function isClientActive(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            $isActive = $client && !$client->revoked;

            return response()->json([
                'statut' => 'success',
                'message' => 'Statut du client vérifié avec succès',
                'data' => [
                    'client_id' => $clientId,
                    'is_active' => $isActive,
                    'exists' => $client !== null
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des clients
     */
    public function getClientStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Client::count(),
                'active' => Client::where('revoked', false)->count(),
                'revoked' => Client::where('revoked', true)->count(),
                'personal_access' => Client::where('personal_access_client', true)->count(),
                'password_grant' => Client::where('password_client', true)->count(),
                'authorization_code' => Client::where('personal_access_client', false)
                                             ->where('password_client', false)
                                             ->count(),
            ];

            return response()->json([
                'statut' => 'success',
                'message' => 'Statistiques des clients récupérées avec succès',
                'data' => $stats,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Recherche des clients par nom
     */
    public function searchClients(string $search, int $perPage = 15): JsonResponse
    {
        try {
            $clients = Client::where('name', 'like', '%' . $search . '%')
                           ->orderBy('created_at', 'desc')
                           ->paginate($perPage);

            return response()->json([
                'statut' => 'success',
                'message' => 'Résultats de recherche récupérés avec succès',
                'data' => PassportClientResource::collection($clients)->response()->getData(),
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère les tokens actifs d'un client
     */
    public function getClientActiveTokens(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $tokens = $client->tokens()
                           ->where('revoked', false)
                           ->where('expires_at', '>', now())
                           ->get();

            return response()->json([
                'statut' => 'success',
                'message' => 'Tokens actifs du client récupérés avec succès',
                'data' => $tokens,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Révoque tous les tokens d'un client
     */
    public function revokeClientTokens(string $clientId): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client OAuth non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $count = $client->tokens()->update(['revoked' => true]);

            return response()->json([
                'statut' => 'success',
                'message' => "Tous les tokens du client ont été révoqués ({$count} tokens)",
                'data' => ['revoked_tokens_count' => $count],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    // =============================================================================
    // GESTION DE LA SÉCURITÉ
    // =============================================================================

    /**
     * Rotation automatique des secrets clients anciens
     */
    public function rotateExpiredSecrets(int $daysOld = 90): JsonResponse
    {
        try {
            $expiredClients = Client::where('secret', '!=', null)
                                  ->where('revoked', false)
                                  ->where('updated_at', '<', now()->subDays($daysOld))
                                  ->get();

            $rotatedCount = 0;
            $rotatedClients = [];

            foreach ($expiredClients as $client) {
                $oldSecret = $client->secret;
                $newSecret = Str::random(40);

                $client->update(['secret' => $newSecret]);

                // Log de la rotation
                \Log::info('Client secret rotated', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'old_secret_hash' => hash('sha256', $oldSecret),
                    'new_secret_hash' => hash('sha256', $newSecret),
                    'rotation_date' => now(),
                    'days_since_update' => now()->diffInDays($client->updated_at)
                ]);

                $rotatedClients[] = [
                    'id' => $client->id,
                    'name' => $client->name,
                    'new_secret' => $newSecret,
                    'old_age_days' => now()->diffInDays($client->updated_at)
                ];

                $rotatedCount++;
            }

            return response()->json([
                'statut' => 'success',
                'message' => "Rotation de {$rotatedCount} secrets clients effectuée",
                'data' => [
                    'rotated_count' => $rotatedCount,
                    'rotated_clients' => $rotatedClients
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Force la rotation du secret d'un client spécifique
     */
    public function forceRotateClientSecret(string $clientId, string $reason = 'Manual rotation'): JsonResponse
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Client non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            if (!$client->secret) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Ce client n\'a pas de secret à faire tourner',
                    'errors' => [],
                    'statutCode' => 400
                ], 400);
            }

            $oldSecret = $client->secret;
            $newSecret = Str::random(40);

            $client->update(['secret' => $newSecret]);

            // Révoquer tous les tokens existants pour forcer la réauthentification
            $client->tokens()->update(['revoked' => true]);

            // Log de la rotation forcée
            \Log::warning('Client secret force rotated', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'reason' => $reason,
                'old_secret_hash' => hash('sha256', $oldSecret),
                'new_secret_hash' => hash('sha256', $newSecret),
                'rotation_date' => now(),
                'tokens_revoked' => $client->tokens()->count()
            ]);

            return response()->json([
                'statut' => 'success',
                'message' => 'Secret client tourné avec succès',
                'data' => [
                    'client' => new PassportClientResource($client),
                    'new_secret' => $newSecret,
                    'reason' => $reason,
                    'tokens_revoked' => $client->tokens()->count()
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Nettoie les tokens expirés et révoqués
     */
    public function cleanupExpiredTokens(): JsonResponse
    {
        try {
            // Supprimer les tokens expirés depuis plus de 30 jours
            $expiredTokensCount = DB::table('oauth_access_tokens')
                ->where('expires_at', '<', now()->subDays(30))
                ->delete();

            // Supprimer les tokens révoqués depuis plus de 7 jours
            $revokedTokensCount = DB::table('oauth_access_tokens')
                ->where('revoked', true)
                ->where('updated_at', '<', now()->subDays(7))
                ->delete();

            // Supprimer les refresh tokens expirés
            $expiredRefreshTokensCount = DB::table('oauth_refresh_tokens')
                ->where('expires_at', '<', now())
                ->delete();

            \Log::info('OAuth tokens cleanup completed', [
                'expired_tokens_deleted' => $expiredTokensCount,
                'revoked_tokens_deleted' => $revokedTokensCount,
                'expired_refresh_tokens_deleted' => $expiredRefreshTokensCount,
                'cleanup_date' => now()
            ]);

            return response()->json([
                'statut' => 'success',
                'message' => 'Nettoyage des tokens terminé',
                'data' => [
                    'expired_tokens_deleted' => $expiredTokensCount,
                    'revoked_tokens_deleted' => $revokedTokensCount,
                    'expired_refresh_tokens_deleted' => $expiredRefreshTokensCount,
                    'total_deleted' => $expiredTokensCount + $revokedTokensCount + $expiredRefreshTokensCount
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Audit des accès OAuth
     */
    public function auditOAuthAccess(array $filters = []): JsonResponse
    {
        try {
            $query = DB::table('oauth_access_tokens as oat')
                ->join('oauth_clients as oc', 'oat.client_id', '=', 'oc.id')
                ->leftJoin('users as u', 'oat.user_id', '=', 'u.id')
                ->select([
                    'oat.id as token_id',
                    'oc.name as client_name',
                    'oc.id as client_id',
                    'u.email as user_email',
                    'oat.scopes',
                    'oat.revoked',
                    'oat.created_at as token_created',
                    'oat.expires_at as token_expires',
                    'oat.updated_at as last_used'
                ]);

            // Filtres
            if (isset($filters['client_id'])) {
                $query->where('oc.id', $filters['client_id']);
            }

            if (isset($filters['user_id'])) {
                $query->where('oat.user_id', $filters['user_id']);
            }

            if (isset($filters['revoked'])) {
                $query->where('oat.revoked', $filters['revoked']);
            }

            if (isset($filters['date_from'])) {
                $query->where('oat.created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('oat.created_at', '<=', $filters['date_to']);
            }

            $results = $query->orderBy('oat.created_at', 'desc')
                           ->paginate($filters['per_page'] ?? 50);

            // Statistiques d'audit
            $stats = [
                'total_tokens' => DB::table('oauth_access_tokens')->count(),
                'active_tokens' => DB::table('oauth_access_tokens')->where('revoked', false)->where('expires_at', '>', now())->count(),
                'expired_tokens' => DB::table('oauth_access_tokens')->where('expires_at', '<', now())->count(),
                'revoked_tokens' => DB::table('oauth_access_tokens')->where('revoked', true)->count(),
                'clients_count' => DB::table('oauth_clients')->where('revoked', false)->count()
            ];

            return response()->json([
                'statut' => 'success',
                'message' => 'Audit des accès OAuth récupéré',
                'data' => [
                    'audit_results' => $results,
                    'statistics' => $stats
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    // =============================================================================
    // GESTION DE L'EXPIRATION ET DU RAFRAÎCHISSEMENT DES TOKENS
    // =============================================================================

    /**
     * Vérifie les tokens expirés et les marque comme révoqués
     */
    public function checkExpiredTokens(): JsonResponse
    {
        try {
            $expiredTokens = DB::table('oauth_access_tokens')
                ->where('expires_at', '<', now())
                ->where('revoked', false)
                ->update(['revoked' => true]);

            return response()->json([
                'statut' => 'success',
                'message' => 'Vérification des tokens expirés effectuée',
                'data' => [
                    'expired_tokens_revoked' => $expiredTokens,
                    'check_date' => now()
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Rafraîchit un token d'accès avec un refresh token
     */
    public function refreshAccessToken(string $refreshToken): JsonResponse
    {
        try {
            // Vérifier que le refresh token existe et est valide
            $refreshTokenRecord = DB::table('oauth_refresh_tokens')
                ->where('id', $refreshToken)
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$refreshTokenRecord) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Refresh token invalide ou expiré',
                    'errors' => [],
                    'statutCode' => 401
                ], 401);
            }

            // Récupérer le token d'accès associé
            $accessToken = DB::table('oauth_access_tokens')
                ->where('id', $refreshTokenRecord->access_token_id)
                ->first();

            if (!$accessToken) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Token d\'accès associé non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            // Révoquer l'ancien token d'accès
            DB::table('oauth_access_tokens')
                ->where('id', $accessToken->id)
                ->update(['revoked' => true]);

            // Créer un nouveau token d'accès
            $newTokenId = Str::random(80);
            $expiresAt = now()->addHours(8); // Utilise la configuration d'expiration

            DB::table('oauth_access_tokens')->insert([
                'id' => $newTokenId,
                'user_id' => $accessToken->user_id,
                'client_id' => $accessToken->client_id,
                'name' => $accessToken->name,
                'scopes' => $accessToken->scopes,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            // Créer un nouveau refresh token
            $newRefreshTokenId = Str::random(80);
            $refreshExpiresAt = now()->addHours(3); // Utilise la configuration d'expiration

            // Révoquer l'ancien refresh token
            DB::table('oauth_refresh_tokens')
                ->where('id', $refreshToken)
                ->update(['revoked' => true]);

            // Créer le nouveau refresh token
            DB::table('oauth_refresh_tokens')->insert([
                'id' => $newRefreshTokenId,
                'access_token_id' => $newTokenId,
                'revoked' => false,
                'expires_at' => $refreshExpiresAt,
            ]);

            return response()->json([
                'statut' => 'success',
                'message' => 'Token rafraîchi avec succès',
                'data' => [
                    'access_token' => $newTokenId,
                    'refresh_token' => $newRefreshTokenId,
                    'expires_at' => $expiresAt->toISOString(),
                    'refresh_expires_at' => $refreshExpiresAt->toISOString(),
                    'token_type' => 'Bearer'
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Récupère les informations d'expiration d'un token
     */
    public function getTokenExpirationInfo(string $tokenId): JsonResponse
    {
        try {
            $token = DB::table('oauth_access_tokens')
                ->where('id', $tokenId)
                ->first();

            if (!$token) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Token non trouvé',
                    'errors' => [],
                    'statutCode' => 404
                ], 404);
            }

            $expiresAt = \Carbon\Carbon::parse($token->expires_at);
            $isExpired = $expiresAt->isPast();
            $timeUntilExpiry = $isExpired ? null : now()->diffForHumans($expiresAt, true);

            return response()->json([
                'statut' => 'success',
                'message' => 'Informations d\'expiration récupérées',
                'data' => [
                    'token_id' => $tokenId,
                    'expires_at' => $expiresAt->toISOString(),
                    'is_expired' => $isExpired,
                    'is_revoked' => (bool) $token->revoked,
                    'time_until_expiry' => $timeUntilExpiry,
                    'client_id' => $token->client_id,
                    'user_id' => $token->user_id,
                    'scopes' => json_decode($token->scopes ?? '[]', true)
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Configure les durées d'expiration des tokens
     */
    public function configureTokenExpiration(array $config): JsonResponse
    {
        try {
            $validatedConfig = [];

            // Valider les durées d'expiration
            if (isset($config['access_token_lifetime_hours'])) {
                $hours = (int) $config['access_token_lifetime_hours'];
                if ($hours < 1 || $hours > 168) { // Entre 1 heure et 7 jours
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'La durée de vie des tokens d\'accès doit être entre 1 et 168 heures',
                        'errors' => [],
                        'statutCode' => 400
                    ], 400);
                }
                $validatedConfig['access_token_lifetime_hours'] = $hours;
            }

            if (isset($config['refresh_token_lifetime_hours'])) {
                $hours = (int) $config['refresh_token_lifetime_hours'];
                if ($hours < 1 || $hours > 720) { // Entre 1 heure et 30 jours
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'La durée de vie des refresh tokens doit être entre 1 et 720 heures',
                        'errors' => [],
                        'statutCode' => 400
                    ], 400);
                }
                $validatedConfig['refresh_token_lifetime_hours'] = $hours;
            }

            if (isset($config['personal_access_token_lifetime_days'])) {
                $days = (int) $config['personal_access_token_lifetime_days'];
                if ($days < 1 || $days > 365) { // Entre 1 jour et 1 an
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'La durée de vie des tokens d\'accès personnel doit être entre 1 et 365 jours',
                        'errors' => [],
                        'statutCode' => 400
                    ], 400);
                }
                $validatedConfig['personal_access_token_lifetime_days'] = $days;
            }

            // Sauvegarder dans la configuration (ou base de données selon l'architecture)
            foreach ($validatedConfig as $key => $value) {
                \Config::set("passport.{$key}", $value);
            }

            return response()->json([
                'statut' => 'success',
                'message' => 'Configuration des durées d\'expiration mise à jour',
                'data' => [
                    'updated_config' => $validatedConfig,
                    'current_config' => [
                        'access_token_lifetime_hours' => config('passport.access_token_lifetime_hours', 8),
                        'refresh_token_lifetime_hours' => config('passport.refresh_token_lifetime_hours', 3),
                        'personal_access_token_lifetime_days' => config('passport.personal_access_token_lifetime_days', 15)
                    ]
                ],
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Obtient les statistiques d'expiration des tokens
     */
    public function getTokenExpirationStats(): JsonResponse
    {
        try {
            $stats = [
                'total_tokens' => DB::table('oauth_access_tokens')->count(),
                'active_tokens' => DB::table('oauth_access_tokens')
                    ->where('revoked', false)
                    ->where('expires_at', '>', now())
                    ->count(),
                'expired_tokens' => DB::table('oauth_access_tokens')
                    ->where('expires_at', '<', now())
                    ->count(),
                'revoked_tokens' => DB::table('oauth_access_tokens')
                    ->where('revoked', true)
                    ->count(),
                'expiring_soon' => DB::table('oauth_access_tokens')
                    ->where('revoked', false)
                    ->where('expires_at', '>', now())
                    ->where('expires_at', '<', now()->addHour())
                    ->count(),
                'refresh_tokens_active' => DB::table('oauth_refresh_tokens')
                    ->where('revoked', false)
                    ->where('expires_at', '>', now())
                    ->count(),
                'refresh_tokens_expired' => DB::table('oauth_refresh_tokens')
                    ->where('expires_at', '<', now())
                    ->count()
            ];

            return response()->json([
                'statut' => 'success',
                'message' => 'Statistiques d\'expiration des tokens récupérées',
                'data' => $stats,
                'statutCode' => 200
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => 500
            ], 500);
        }
    }

    /**
     * Gère l'authentification et l'activation via Active Directory
     *
     * @param array $adUserInfo Informations de l'utilisateur depuis l'AD
     * @param array|null $stateData Données du state (optionnel)
     * @return array
     */
    public function handleADAuthentication(array $adUserInfo, ?array $stateData = null): array
    {
        DB::beginTransaction();

        try {
            $email = $adUserInfo['email'] ?? null;

            if (!$email) {
                return [
                    'success' => false,
                    'message' => 'Email manquant dans les données Active Directory'
                ];
            }

            // Vérifier si l'utilisateur existe dans notre système
            $utilisateur = $this->repository->findByAttribute('email', $email);

            if (!$utilisateur) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé dans le système BIP. Veuillez contacter votre administrateur.'
                ];
            }

            // Mettre à jour les informations de l'utilisateur avec les données de l'AD
            if (isset($adUserInfo['given_name']) && $utilisateur->personne) {
                $utilisateur->personne->prenom = $adUserInfo['given_name'];
            }
            if (isset($adUserInfo['family_name']) && $utilisateur->personne) {
                $utilisateur->personne->nom = $adUserInfo['family_name'];
            }
            if (isset($adUserInfo['name']) && $utilisateur->personne && !isset($adUserInfo['given_name'])) {
                // Si on a le nom complet mais pas given_name/family_name séparés
                $nameParts = explode(' ', $adUserInfo['name'], 2);
                $utilisateur->personne->prenom = $nameParts[0] ?? '';
                $utilisateur->personne->nom = $nameParts[1] ?? '';
            }
            if ($utilisateur->personne) {
                $utilisateur->personne->save();
            }

            // Vérifier si le compte est déjà activé
            $compteDejaActive = $utilisateur->email_verified_at !== null;

            // Activer le compte si pas encore activé
            if (!$compteDejaActive) {
                $utilisateur->email_verified_at = now();
                $utilisateur->statut = 1;
                $utilisateur->first_connexion = now();

                // Nettoyer les données de vérification
                if ($stateData && isset($stateData['activation_token'])) {
                    $utilisateur->account_verification_request_sent_at = null;
                    $utilisateur->link_is_valide = false;
                    $utilisateur->token = null;
                }
            }

            $utilisateur->last_connection = now();
            $utilisateur->save();

            // Générer le token d'authentification BIP (Passport)
            $bipToken = $utilisateur->createToken('Bip-Token')->toArray();

            $utilisateur->lastRequest = date('Y-m-d H:i:s');
            $utilisateur->save();

            DB::commit();

            // Log de l'activation
            $acteur = $utilisateur->personne ? $utilisateur->personne->nom . " " . $utilisateur->personne->prenom : "Inconnu";
            $message = $compteDejaActive
                ? Str::ucfirst($acteur) . " s'est connecté via AD."
                : Str::ucfirst($acteur) . " a activé son compte et s'est connecté via AD.";

            Log::info($message, [
                'user_id' => $utilisateur->id,
                'email' => $utilisateur->email,
                'compte_active' => !$compteDejaActive
            ]);

            return [
                'success' => true,
                'data' => $bipToken,
                'user' => $utilisateur,
                'compte_active' => !$compteDejaActive,
                'message' => $compteDejaActive
                    ? 'Authentification réussie via Active Directory'
                    : 'Compte activé et authentification réussie via Active Directory'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Erreur lors de l\'authentification AD: ' . $th->getMessage(), [
                'email' => $adUserInfo['email'] ?? 'unknown',
                'trace' => $th->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    /**
     * Gère le callback de l'Active Directory après authentification
     *
     * @param string $code Code d'autorisation de l'AD
     * @param string $state État contenant les informations de l'utilisateur
     * @return JsonResponse
     */
    public function handleProviderCallback(string $code, string $state): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Décrypter le state pour obtenir les informations de l'utilisateur
            $stateData = json_decode(base64_decode($state), true);

            if (!$stateData || !isset($stateData['email'])) {
                throw new Exception("State invalide ou manquant", 400);
            }

            // Échanger le code contre un token AD
            $tokenResponse = Http::asForm()->post(
                config('keycloak.base_url') . '/realms/' . config('keycloak.realm') . '/protocol/openid-connect/token',
                [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('keycloak.client_id'),
                    'client_secret' => config('keycloak.client_secret'),
                    'code' => $code,
                    'redirect_uri' => config('keycloak.redirect_uri')
                ]
            );

            if (!$tokenResponse->successful()) {
                throw new Exception("Échec de l'échange du code d'autorisation", 400);
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'];

            // Obtenir les informations de l'utilisateur depuis l'AD
            $userInfoResponse = Http::withToken($accessToken)
                ->get(config('keycloak.base_url') . '/realms/' . config('keycloak.realm') . '/protocol/openid-connect/userinfo');

            if (!$userInfoResponse->successful()) {
                throw new Exception("Impossible de récupérer les informations utilisateur", 400);
            }

            $adUserInfo = $userInfoResponse->json();
            $email = $adUserInfo['email'] ?? $stateData['email'];

            // Vérifier si l'utilisateur existe dans notre système
            $utilisateur = $this->repository->findByAttribute('email', $email);

            if (!$utilisateur) {
                DB::rollBack();
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Utilisateur non trouvé dans le système BIP. Veuillez contacter votre administrateur.',
                    'errors' => []
                ], 404);
            }

            // Mettre à jour les informations de l'utilisateur avec les données de l'AD
            if (isset($adUserInfo['given_name']) && $utilisateur->personne) {
                $utilisateur->personne->prenom = $adUserInfo['given_name'];
            }
            if (isset($adUserInfo['family_name']) && $utilisateur->personne) {
                $utilisateur->personne->nom = $adUserInfo['family_name'];
            }
            if ($utilisateur->personne) {
                $utilisateur->personne->save();
            }

            // Vérifier si le compte est déjà activé
            $compteDejaActive = $utilisateur->email_verified_at !== null;

            // Activer le compte si pas encore activé
            if (!$compteDejaActive) {
                $utilisateur->email_verified_at = now();
                $utilisateur->statut = 1;
                $utilisateur->first_connexion = now();

                // Nettoyer les données de vérification
                if (isset($stateData['activation_token'])) {
                    $utilisateur->account_verification_request_sent_at = null;
                    $utilisateur->link_is_valide = false;
                    $utilisateur->token = null;
                }
            }

            $utilisateur->last_connection = now();
            $utilisateur->save();

            // Générer le token d'authentification BIP (Passport)
            $bipToken = $utilisateur->createToken('Bip-Token')->toArray();

            $utilisateur->lastRequest = date('Y-m-d H:i:s');
            $utilisateur->save();

            DB::commit();

            // Log de l'activation
            $acteur = $utilisateur->personne ? $utilisateur->personne->nom . " " . $utilisateur->personne->prenom : "Inconnu";
            $message = $compteDejaActive
                ? Str::ucfirst($acteur) . " s'est connecté via AD."
                : Str::ucfirst($acteur) . " a activé son compte et s'est connecté via AD.";

            \Log::info($message, [
                'user_id' => $utilisateur->id,
                'email' => $utilisateur->email,
                'compte_active' => !$compteDejaActive
            ]);

            return response()->json([
                'statut' => 'success',
                'message' => $compteDejaActive
                    ? 'Authentification réussie via Active Directory'
                    : 'Compte activé et authentification réussie via Active Directory',
                'data' => new LoginResource($bipToken),
                'compte_nouvellement_active' => !$compteDejaActive,
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();

            \Log::error('Erreur lors du callback AD: ' . $th->getMessage(), [
                'code' => $code,
                'state' => $state,
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => []
            ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
