<?php

namespace App\Services;

use App\Http\Resources\DpafResource;
use App\Jobs\SendEmailJob;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Repositories\Contracts\DpafRepositoryInterface;
use App\Services\Contracts\DpafServiceInterface;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use App\Traits\GenerateTemporaryPassword;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DpafService extends BaseService implements DpafServiceInterface
{
    use GenerateTemporaryPassword;

    protected BaseRepositoryInterface $repository;
    protected UserRepositoryInterface $userRepository;
    protected RoleRepositoryInterface $roleRepository;
    protected PersonneRepositoryInterface $personneRepository;

    public function __construct(
        DpafRepositoryInterface $repository,
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository,
        PersonneRepositoryInterface $personneRepository
    ) {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->personneRepository = $personneRepository;
    }

    protected function getResourceClass(): string
    {
        return DpafResource::class;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $dpaf = $this->repository->first();

            if($dpaf){
                $dpaf = $dpaf->fill($attributs);
            }
            else{
                $dpaf = $this->repository->fill($attributs);
            }

            $dpaf->save();

            if($dpaf->user){
                if($dpaf->user->personne){
                    $attributs["organismeId"] = null;
                    $dpaf->user->personne->fill($attributs);
                    $dpaf->user->personne->save();
                }
            }
            else{

                $personneData = $attributs["admin"]['personne'] ?? [];
                // Création de la personne
                $personne = $this->personneRepository->create(array_merge($personneData, ["organismeId" => null]));

                $role = $this->roleRepository->findByAttribute('slug', 'dpaf');

                $password = $this->generateSimpleTemporaryPassword();

                $dpaf->user()->create(array_merge($attributs["admin"], ['password' => Hash::make($password), "username" => $attributs["admin"]['email'], "provider_user_id" => $attributs["admin"]['email'], "personneId" => $personne->id, "roleId" => $role->id, 'type' => $role->slug, 'profilable_type' => get_class($dpaf), 'profilable_id' => $dpaf->id]));

                $dpaf->refresh();

                // Création de la personne

                $dpaf->user->roles()->attach([$role->id]);

                $utilisateur = $dpaf->user;

                $utilisateur->account_verification_request_sent_at = Carbon::now();

                $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make( $utilisateur->id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

                $utilisateur->link_is_valide = true;

                $utilisateur->save();

                //Envoyer les identifiants de connexion à l'utilisateur via son email
                dispatch(new SendEmailJob($dpaf->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            }

            $dpaf->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé le compte admin de la dpaf {$dpaf->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($uniteeDeGestion), $uniteeDeGestion->id);

            return response()->json(['statut' => 'success', 'message' => "Compte dpaf créé", 'data' => $dpaf, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($dpafId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();
        try {

            if(is_string($dpafId))
            {
                $dpaf = $this->repository->findById($dpafId);
            }
            else{
                $dpaf = $dpafId;
            }

            unset($attributs['email']);
            unset($attributs['username']);
            if(isset($attributs["admin"]["personne"]["organismeId"])){
                unset($attributs["admin"]["personne"]["organismeId"]);
            }

            $dpaf->fill($attributs)->save();

            $dpaf->user->personne->fill($attributs["admin"]["personne"])->save();

            $dpaf->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte de la dpaf {$dpaf->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($uniteeDeGestion), $uniteeDeGestion->id);

            return response()->json(['statut' => 'success', 'message' => "Compte dpaf modifié", 'data' => $dpaf, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}