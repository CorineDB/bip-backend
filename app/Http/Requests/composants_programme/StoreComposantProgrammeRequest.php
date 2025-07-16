<?php

namespace App\Http\Requests\ComposantsProgramme;

use Illuminate\Foundation\Http\FormRequest;

class StoreComposantProgrammeRequest extends FormRequest
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