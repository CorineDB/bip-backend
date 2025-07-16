<?php

namespace App\Http\Requests\TypesFinancement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTypeFinancementRequest extends FormRequest
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
