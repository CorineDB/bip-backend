<?php

namespace App\Http\Requests\categories_projet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategorieProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categorieId = $this->route('categorie_projet') ?? $this->route('id');

        return [
            'categorie'=> ['sometimes', 'string', Rule::unique('categories_projet', 'categorie')->ignore($categorieId)->whereNull('deleted_at')],
        ];
    }
}
