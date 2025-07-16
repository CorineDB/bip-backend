<?php

namespace App\Http\Requests\Arrondissements;

use Illuminate\Foundation\Http\FormRequest;

class StoreArrondissementRequest extends FormRequest
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