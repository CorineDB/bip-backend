<?php

namespace App\Http\Requests\Departements;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartementRequest extends FormRequest
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