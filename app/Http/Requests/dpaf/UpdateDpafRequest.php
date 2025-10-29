<?php

namespace App\Http\Requests\dpaf;

use App\Models\Organisation;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDpafRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['organisation']) || (in_array($user->profilable_type, [Organisation::class]) && ($user->hasPermissionTo('modifier-la-dpaf') || $user->hasPermissionTo('gerer-la-dpaf')) ));
    }

    public function rules(): array
    {
        $dpafId = $this->route('dpaf') ? ((is_string($this->route('dpaf'))  || is_numeric($this->route('dpaf'))) ? $this->route('dpaf') : ($this->route('dpaf')->id)) : $this->route('id');

        $user = $this->user();
        $isOrganisationItself = $user && $user->profilable_type === Organisation::class;

        $idMinistereRules = [];

        // Si ce n'est pas l'organisation elle-même, id_ministere est requis
        if (!$isOrganisationItself) {
            $idMinistereRules[] = 'required';
        }

        $idMinistereRules[] = new HashedExists(Organisation::class, 'id', function ($query) {
            $query->where('type', 'ministere')->whereNull('deleted_at');
        });

        $idMinistereRules[] = Rule::unique('dpaf', 'id_ministere')->ignore($dpafId)->whereNull('deleted_at');

        return [
            'nom' => ['required', 'string'],
            'description' => 'nullable|string',
            'id_ministere' => $idMinistereRules,

            "admin" => ["required"],
            // Attributs de personne
            'admin.personne.nom' => 'required|string|max:255',
            'admin.personne.prenom' => 'required|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }
}
