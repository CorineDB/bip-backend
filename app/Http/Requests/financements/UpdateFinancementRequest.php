<?php

namespace App\Http\Requests\financements;

use App\Enums\EnumTypeFinancement;
use App\Models\Dgpd;
use App\Models\Financement;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('modifier-un-financement') || $user->hasPermissionTo('gerer-les-financements')) ));
    }

    protected function prepareForValidation(): void
    {
        $financementId = $this->route('financement');

        if ($financementId && is_string($financementId) && !is_numeric($financementId)) {
            $financementId = Financement::unhashId($financementId);
            $this->merge(['_financement_id' => $financementId]);
        }
    }

    public function rules(): array
    {
        $financementId = $this->input('_financement_id') ?? $this->route('financement');

        return [
            'nom'=> ['required', 'string', Rule::unique('financements', 'nom')->ignore($financementId)->whereNull('deleted_at')],
            'nom_usuel' => 'required|string',
            'type' => ['required', Rule::in(EnumTypeFinancement::values())],
            'financementId' => [Rule::requiredIf($this->input("type") != "type"), 'sometimes', new HashedExists(Financement::class), 'different:' . $financementId,

                /* function ($attribute, $value, $fail) {
                    $exists = Financement::findByHashedId("id", $value)->when($this->input("type") == "secteur", function($query){
                        $query->whereNull('financementId')->where('type', 'type');
                    })->when($this->input("type") == "nature", function($query){

                        $query->where('type', 'source')->whereHas('parent', function ($query) {
                            $query->where('type', 'type');
                        });
                    })->whereNull('deleted_at')->exists();

                    if (!$exists && $this->input("type") == "source") {
                        $fail('Le Type de financement est inconnue');
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
            'slug.required' => 'Le slug du financement est obligatoire.',
            'slug.string' => 'Le slug du financement doit être une chaîne de caractères.',
            'slug.max' => 'Le slug du financement ne peut pas dépasser 255 caractères.',
            'slug.unique' => 'Ce slug de financement existe déjà.',
            'slug.regex' => 'Le slug ne peut contenir que des lettres minuscules, chiffres, tirets et underscores.',
            'type.required' => 'Le type de financement est obligatoire.',
            'type.in' => 'Le type de financement sélectionné n\'est pas valide. Les valeurs autorisées sont : ' . implode(', ', EnumTypeFinancement::values()),
            'financementId.required' => 'L\'ID du financement parent est obligatoire.',
            'financementId.integer' => 'L\'ID du financement parent doit être un nombre entier.',
            'financementId.exists' => 'Le financement parent sélectionné n\'existe pas.',
            'financementId.different' => 'Un financement ne peut pas être son propre parent.'
        ];
    }
}
