<?php

namespace App\Http\Requests\CategoriesProjet;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategorieProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: add validation rules
        ];
    }
}