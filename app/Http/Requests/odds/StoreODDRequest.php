<?php

namespace App\Http\Requests\Odds;

use Illuminate\Foundation\Http\FormRequest;

class StoreODDRequest extends FormRequest
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