<?php

namespace App\Http\Requests\IdeesProjet;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeeProjetRequest extends FormRequest
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
