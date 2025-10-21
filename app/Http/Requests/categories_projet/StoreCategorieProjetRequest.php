<?php

namespace App\Http\Requests\categories_projet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategorieProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'categorie'=> ['required', 'string', Rule::unique('categories_projet', 'categorie')->whereNull('deleted_at')],
        ];
    }
}
