<?php

namespace App\Http\Requests\Canevas;

use Illuminate\Foundation\Http\FormRequest;

class StoreCanevasRequest extends FormRequest
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