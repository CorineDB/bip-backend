<?php

namespace App\Http\Requests\categories_projet;

use App\Models\Dgpd;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategorieProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('modifier-une-categorie-de-projet') || $user->hasPermissionTo('gerer-les-categories-de-projet')) ));
    }

    public function rules(): array
    {
        $categorieId = $this->route('categorie_projet') ?? $this->route('id');

        return [
            'categorie'=> ['sometimes', 'string', Rule::unique('categories_projet', 'categorie')->ignore($categorieId)->whereNull('deleted_at')],
        ];
    }
}
