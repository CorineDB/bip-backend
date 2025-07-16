<?php

namespace App\Http\Requests\TypesIntervention;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTypeInterventionRequest extends FormRequest
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
