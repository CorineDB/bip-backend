<?php

namespace App\Http\Requests\users;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Define your validation rules here
        ];
    }

    public function messages(): array
    {
        return [
            // Custom validation messages (optional)
        ];
    }
}