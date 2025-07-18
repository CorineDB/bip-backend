<?php

namespace App\Http\Requests\Personnes;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonneRequest extends FormRequest
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