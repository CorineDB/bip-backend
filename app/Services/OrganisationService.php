<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Contracts\OrganisationServiceInterface;
use App\Http\Resources\OrganisationResource;
use App\Jobs\SendEmailJob;
use App\Repositories\Contracts\OrganisationRepositoryInterface;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Traits\GenerateTemporaryPassword;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganisationService extends BaseService implements OrganisationServiceInterface
{
    use GenerateTemporaryPassword;

    protected PersonneRepositoryInterface $personneRepository;
    protected RoleRepositoryInterface $roleRepository;


    public function __construct(
        OrganisationRepositoryInterface $repository,
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
        return OrganisationResource::class;
    }

    public function update($organisationId, array $attributs): JsonResponse
    {
        DB::beginTransaction();
        try {

            if (is_string($organisationId)) {
                $organisation = $this->repository->findById($organisationId);
            } else {
                $organisation = $organisationId;
            }

            $organisation->fill($attributs)->save();

            if ($organisation->user) {
                $attributs["admin"]["personne"]["organismeId"] = $organisation->id;
                $organisation->user->personne->fill($attributs["admin"]["personne"])->save();
            } else if(isset($attributs["admin"])){
                $personneData = $attributs["admin"]['personne'] ?? [];

                // Création de la personne
                $personne = $this->personneRepository->create(array_merge($personneData, ["organismeId" => $organisation->id]));

                $role = $this->roleRepository->findByAttribute('slug', 'organisation');

                if (!$role) throw new Exception("Role introuvable", 400);

                $password = $this->generateSimpleTemporaryPassword();

                $organisation->user()->create(array_merge($attributs["admin"], ['password' => Hash::make($password), "username" => $attributs["admin"]['email'], "provider_user_id" => $attributs["admin"]['email'], "personneId" => $personne->id, "roleId" => $role->id, 'type' => $role->slug, 'profilable_type' => get_class($organisation), 'profilable_id' => $organisation->id]));

                $organisation->refresh();

                // Création de la personne

                $organisation->user->roles()->attach([$role->id]);

                $utilisateur = $organisation->user;

                $utilisateur->account_verification_request_sent_at = Carbon::now();

                $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make($utilisateur->id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

                $utilisateur->link_is_valide = true;

                $utilisateur->save();

                //Envoyer les identifiants de connexion à l'utilisateur via son email
                dispatch(new SendEmailJob($organisation->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));
            }

            $organisation->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte de l'organisation {$organisation->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($uniteeDeGestion), $uniteeDeGestion->id);

            return response()->json(['statut' => 'success', 'message' => "Compte organisation modifié", 'data' => $organisation, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ministeres(): JsonResponse
    {
        try {
            $data = $this->repository->getModel()->where("type", "ministere")->whereNull("parentId")->get();
            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function organismes_de_tutelle($idMinistere): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idMinistere)->children;
            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
