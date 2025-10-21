<?php

namespace App\Jobs;

use App\Mail\ConfirmationDeCompteEmail;
use App\Mail\ReinitialisationMotDePasseEmail;
use App\Models\User;
use App\Services\Traits\HelperTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use HelperTrait;

    private $user;
    private $type;
    private $mailer;
    private $password;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $type, $password = null)
    {
        $this->user = $user;
        $this->type = $type;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $details = [];
            $data = [];

            Log::notice("LOG TEST");

            $lien = config("mail.client_app.url") ?? config("app.url");

            Log::notice($lien);

            if ($this->type == "confirmation-compte") {
                $details['view'] = "emails.auth.confirmation_compte";
                $details['subject'] = "Activation de votre compte BIP - Bienvenue";

                // Générer le lien de connexion AD en utilisant la même logique que /ad-auth/redirect
                $state = \Illuminate\Support\Str::uuid()->toString();
                $callbackUrl = config('services.gov.redirect');

                // Stocker des informations supplémentaires dans le state pour l'activation
                \Illuminate\Support\Facades\Cache::put("oauth_state:{$state}", [
                    'frontend_origin' => config('mail.client_app.url') ?? config('app.url'),
                    'email' => $this->user->email,
                    'activation_mode' => true,
                    'user_id' => $this->user->id
                ], 300); // 5 minutes

                $params = http_build_query([
                    'client_id' => config('services.gov.client_id'),
                    'redirect_uri' => $callbackUrl,
                    'response_type' => 'code',
                    'scope' => 'openid',
                    'state' => $state,
                    'authError' => 'true',
                ]);

                $loginLink = config('services.gov.url') . '/official/login?' . $params;

                $details['content'] = [
                    "greeting" => "Bienvenu Mr/Mme " . $this->user->personne->nom,
                    "introduction" => "Votre compte BIP a été créé avec succès. Veuillez cliquer sur le lien ci-dessous pour vous connecter avec Active Directory et activer votre compte",
                    "identifiant" => $this->user->email,
                    "password" => $this->password,
                    "lien" => $loginLink,
                    "lien_label" => "Se connecter à BIP"
                ];
                Log::notice("Lien de connexion AD généré: " . $loginLink);
                $mailer = new ConfirmationDeCompteEmail($details);
            } elseif ($this->type == "confirmation-de-compte") {

                $details['view'] = "emails.auth.confirmation_de_compte";
                $details['subject'] = "Confirmez votre inscription sur BIP - Action requise";

                // Générer le lien de connexion AD pour activation
                $state = \Illuminate\Support\Str::uuid()->toString();
                $callbackUrl = config('services.gov.redirect');

                // Stocker des informations pour l'activation
                \Illuminate\Support\Facades\Cache::put("oauth_state:{$state}", [
                    'frontend_origin' => config('mail.client_app.url') ?? config('app.url'),
                    'email' => $this->user->email,
                    'activation_token' => $this->user->token,
                    'activation_mode' => true,
                    'user_id' => $this->user->hashe_id
                ], 300); // 5 minutes

                $params = http_build_query([
                    'client_id' => config('services.gov.client_id'),
                    'redirect_uri' => $callbackUrl,
                    'response_type' => 'code',
                    'scope' => 'openid',
                    'state' => $state,
                    'authError' => 'true',
                ]);

                $activationLink = config('services.gov.url') . '/official/login?' . $params;

                $details['content'] = [
                    "greeting" => "Bienvenu Mr/Mme " . $this->user->personne->nom,
                    "introduction" => "Veuillez cliquer sur le lien ci-dessous pour vous connecter avec Active Directory et activer votre compte BIP",
                    "lien" => $activationLink,
                    "lien_label" => "Activer mon compte"
                ];
                Log::notice("Lien d'activation AD généré: " . $activationLink);
                $mailer = new ConfirmationDeCompteEmail($details);
            } elseif ($this->type == "reinitialisation-mot-de-passe") {

                $details['view'] = "emails.auth.reinitialisation_mot_passe";
                $details['subject'] = "Réinitialisation de passe BIP";
                $details['content'] = [
                    "greeting" => "Bienvenu Mr/Mme " . $this->user->personne->nom,
                    "introduction" => "Voici votre lien de réinitialisation",
                    "lien" => $lien . "/reset_password/" . $this->user->token,
                ];

                $mailer = new ReinitialisationMotDePasseEmail($details);
            }

            /*
                elseif ($this->type == "rappel-ano") {
                    $details['view'] = "emails.ano.rappel";
                    $details['subject'] = "Rappel de traitement d'une demande d'ano";
                    $details['content'] = [
                        "greeting" => "Demande d'ano",
                        "introduction" => "Une demande d'ano est entente de validation",
                    ];
                    $mailer = new AnoEmail($details);
                } elseif ($this->type == "demande-ano") {
                    $details['view'] = "emails.ano.demande";
                    $details['subject'] = "Nouvelle demande d'ano";
                    $details['content'] = [
                        "greeting" => "Demande d'ano",
                        "introduction" => "Une nouvelle demande d'ano vient d'être soumis",
                    ];
                    $mailer = new AnoEmail($details);

                } elseif ($this->type == "reponse-ano") {
                    $details['view'] = "emails.ano.reponse";
                    $details['subject'] = "Reponse suite à la demande d'ano";
                    $details['content'] = [
                        "greeting" => "Reponse à la demande d'ano",
                        "introduction" => "Une nouvelle demande d'ano vient d'être soumis",
                    ];
                    $mailer = new AnoEmail($details);
                }
            */

            $when = now()->addSeconds(5);

            Log::notice($this->user->email);

            Log::notice($lien);

            Mail::to($this->user->email)->later($when, $mailer);
        } catch (\Throwable $th) {
            Log::error($details['subject'] . ' : ' . $th->getMessage());
            throw new Exception("Error Processing Request : ". json_encode($details['subject']. " : ". $th->getMessage()), 1);
            //throw new Exception("Error Processing Request : ". $details['subject'], 1);
        }
    }
}
