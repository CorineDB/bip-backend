<?php

namespace App\Http\Requests\SourcesFinancement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSourceFinancementRequest extends FormRequest
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
