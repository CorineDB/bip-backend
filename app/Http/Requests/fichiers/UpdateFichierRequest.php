<?php

namespace App\Http\Requests\fichiers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFichierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // TODO: add validation rules
        ];
    }
}
