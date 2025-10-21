<?php

namespace App\Http\Requests\evaluations;

use Illuminate\Foundation\Http\FormRequest;

class AssignEvaluateursRequest extends FormRequest
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
            'evaluateur_ids' => 'required|array|min:1',
            'evaluateur_ids.*' => 'required|integer|exists:users,id|distinct',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'evaluateur_ids.required' => 'Au moins un évaluateur doit être sélectionné.',
            'evaluateur_ids.array' => 'Les évaluateurs doivent être fournis sous forme de tableau.',
            'evaluateur_ids.min' => 'Au moins un évaluateur doit être sélectionné.',
            'evaluateur_ids.*.exists' => 'Un des évaluateurs sélectionnés n\'existe pas.',
            'evaluateur_ids.*.distinct' => 'Les évaluateurs ne peuvent pas être dupliqués.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'evaluateur_ids' => 'évaluateurs',
            'evaluateur_ids.*' => 'évaluateur',
        ];
    }
}
