<?php

namespace App\Http\Requests\idees_projet;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdeeProjetRequest extends FormRequest
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
