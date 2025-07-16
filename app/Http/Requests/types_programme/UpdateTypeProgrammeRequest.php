<?php

namespace App\Http\Requests\TypesProgramme;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTypeProgrammeRequest extends FormRequest
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
