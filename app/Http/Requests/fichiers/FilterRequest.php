<?php

namespace App\Http\Requests\fichiers;

use App\Enums\StatutIdee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_by_folder' => ['boolean:true'],
            'dossier_id' => ['sometimes', Rule::exists("dossiers","id")->whereNull("deleted_at")],
        ];
    }
}
