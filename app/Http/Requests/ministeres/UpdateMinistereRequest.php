<?php

namespace App\Http\Requests\Ministeres;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMinistereRequest extends FormRequest
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