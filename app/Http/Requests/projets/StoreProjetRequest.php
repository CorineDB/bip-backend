<?php

namespace App\Http\Requests\projets;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            // TODO: add validation rules
        ];
    }
}
