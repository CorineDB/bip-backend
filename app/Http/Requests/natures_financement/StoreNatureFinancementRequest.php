<?php

namespace App\Http\Requests\NaturesFinancement;

use Illuminate\Foundation\Http\FormRequest;

class StoreNatureFinancementRequest extends FormRequest
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