<?php

namespace App\Http\Requests\evaluation_criteres;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationCritereRequest extends FormRequest
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