<?php

namespace App\Http\Requests\Cibles;

use Illuminate\Foundation\Http\FormRequest;

class StoreCibleRequest extends FormRequest
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