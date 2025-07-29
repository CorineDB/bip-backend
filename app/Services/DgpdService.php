<?php

namespace App\Services;

use App\Http\Resources\DgpdResource;
use App\Jobs\SendEmailJob;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Repositories\Contracts\DgpdRepositoryInterface;
use App\Services\Contracts\DgpdServiceInterface;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use App\Traits\GenerateTemporaryPassword;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DgpdService extends BaseService implements DgpdServiceInterface
{
    use GenerateTemporaryPassword;

    protected BaseRepositoryInterface $repository;
    protected PersonneRepositoryInterface $personneRepository;
    protected RoleRepositoryInterface $roleRepository;


    public function __construct(
        DgpdRepositoryInterface $repository,
        PersonneRepositoryInterface $personneRepository,
        RoleRepositoryInterface $roleRepository
    ) {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->roleRepository = $roleRepository;
        $this->personneRepository = $personneRepository;

    }

    protected function getResourceClass(): string
    {
        return DgpdResource::class;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $dgpd = $this->repository->first();

            if($dgpd){
                $dgpd = $dgpd->fill($attributs);
            }
            else{
                $dgpd = $this->repository->fill($attributs);
            }

            $dgpd->save();

            if($dgpd->user){
                if($dgpd->user->personne){
                    $attributs["organismeId"] = null;
                    $dgpd->user->personne->fill($attributs);
                    $dgpd->user->personne->save();
                }
            }
        else{

                $personneData = $attributs["admin"]['personne'] ?? [];
                // Création de la personne
                $personne = $this->personneRepository->create(array_merge($personneData, ["organismeId" => null]));

                $role = $this->roleRepository->findByAttribute('slug', 'dgpd');

                $password = $this->generateSimpleTemporaryPassword();

                $dgpd->user()->create(array_merge($attributs["admin"], ['password' => Hash::make($password), "username" => $attributs["admin"]['email'], "provider_user_id" => $attributs["admin"]['email'], "personneId" => $personne->id, "roleId" => $role->id, 'type' => $role->slug, 'profilable_type' => get_class($dgpd), 'profilable_id' => $dgpd->id]));

                $dgpd->refresh();

                // Création de la personne

                $dgpd->user->roles()->attach([$role->id]);

                $utilisateur = $dgpd->user;

                $utilisateur->account_verification_request_sent_at = Carbon::now();

                $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make( $utilisateur->id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

                $utilisateur->link_is_valide = true;

                $utilisateur->save();

                //Envoyer les identifiants de connexion à l'utilisateur via son email
                dispatch(new SendEmailJob($dgpd->user, "confirmation-compte", $password))->delay(now()->addSeconds(15));

            }

            $dgpd->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé le compte admin de la d {$dgpd->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($uniteeDeGestion), $uniteeDeGestion->id);

            return response()->json(['statut' => 'success', 'message' => "Compte d créé", 'data' => $dgpd, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($dgpdId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();
        try {

            if(is_string($dgpdId))
            {
                $dgpd = $this->repository->findById($dgpdId);
            }
            else{
                $dgpd = $dgpdId;
            }

            unset($attributs["admin"]['email']);
            unset($attributs["admin"]['username']);
            if(isset($attributs["admin"]["personne"]["organismeId"])){
                unset($attributs["admin"]["personne"]["organismeId"]);
            }

            $dgpd->fill($attributs)->save();

            $dgpd->user->personne->fill($attributs["admin"]["personne"])->save();

            $dgpd->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte de la d {$dgpd->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($uniteeDeGestion), $uniteeDeGestion->id);

            return response()->json(['statut' => 'success', 'message' => "Compte d modifié", 'data' => $dgpd, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}