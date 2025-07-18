<?php

namespace App\Http\Requests\Champs;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChampRequest extends FormRequest
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