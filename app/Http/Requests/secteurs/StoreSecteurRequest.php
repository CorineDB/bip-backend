<?php

namespace App\Http\Requests\Secteurs;

use Illuminate\Foundation\Http\FormRequest;

class StoreSecteurRequest extends FormRequest
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