<?php

namespace App\Http\Requests\fichiers;

use Illuminate\Foundation\Http\FormRequest;

class StoreFichierRequest extends FormRequest
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