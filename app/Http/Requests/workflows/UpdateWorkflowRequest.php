<?php

namespace App\Http\Requests\workflows;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
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