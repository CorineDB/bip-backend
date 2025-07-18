<?php

namespace App\Http\Requests\Decisions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDecisionRequest extends FormRequest
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