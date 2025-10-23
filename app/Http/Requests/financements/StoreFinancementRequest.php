<?php

namespace App\Http\Requests\financements;

use App\Enums\EnumTypeFinancement;
use App\Models\Dgpd;
use App\Models\Financement;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('creer-un-financement') || $user->hasPermissionTo('gerer-les-financements')) ));
    }

    public function rules(): array
    {
        return [
            'nom'=> ['required', 'string', Rule::unique('financements', 'nom')->whereNull('deleted_at')],
            'nom_usuel' => 'required|string',
            'type' => ['required', Rule::in(EnumTypeFinancement::values())],
            'financementId' => [Rule::requiredIf($this->input("type") != "type"), new HashedExists(Financement::class),

                /* function ($attribute, $value, $fail) {
                    $exists = Financement::with(['parent'])->findByHashedId("id", $value)->when($this->input("type") == "secteur", function($query){
                        $query->whereNull('financementId')->where('type', 'type');
                    })->when($this->input("type") == "nature", function($query){

                        $query->with("parent")->where('type', 'source')->whereHas('parent', function ($query) {
                            $query->where('type', 'type');
                        });
                    })->whereNull('deleted_at')->exists();

                    if (!$exists && $this->input("type") == "source") {
                        $fail('Le Type de financement est inconnu');
                    }
                    else if (!$exists && $this->input("type") == "nature") {
                        $fail('La source de financement est inconnue');
                    }
                } */
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du financement est obligatoire.',
            'nom.string' => 'Le nom du financement doit être une chaîne de caractères.',
            'nom.unique' => 'Ce nom de financement existe déjà.',
            'nom_usuel.required' => 'Le nom usuel du financement est obligatoire.',
            'nom_usuel.string' => 'Le nom usuel du financement doit être une chaîne de caractères.',
            'type.required' => 'Le type de financement est obligatoire.',
            'type.in' => 'Le type de financement sélectionné n\'est pas valide. Les valeurs autorisées sont : ' . implode(', ', EnumTypeFinancement::values()),
            'financementId.required' => 'L\'ID du financement parent est obligatoire.',
            'financementId.integer' => 'L\'ID du financement parent doit être un nombre entier.',
            'financementId.exists' => 'Le financement parent sélectionné n\'existe pas.',
            'financementId.different' => 'Un financement ne peut pas être son propre parent.'
        ];
    }
}
