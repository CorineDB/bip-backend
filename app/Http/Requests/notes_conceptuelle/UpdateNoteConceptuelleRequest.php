<?php

namespace App\Http\Requests\notes_conceptuelle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteConceptuelleRequest extends FormRequest
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