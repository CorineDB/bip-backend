<?php

namespace App\Http\Requests\CategoriesCanevas;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategorieCanevasRequest extends FormRequest
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