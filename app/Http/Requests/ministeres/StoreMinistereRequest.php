<?php

namespace App\Http\Requests\Ministeres;

use Illuminate\Foundation\Http\FormRequest;

class StoreMinistereRequest extends FormRequest
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