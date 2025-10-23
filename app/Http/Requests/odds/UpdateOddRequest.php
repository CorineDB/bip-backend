<?php

namespace App\Http\Requests\odds;

use App\Models\Dgpd;
use App\Models\Odd;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOddRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('modifier-un-odd') || $user->hasPermissionTo('gerer-les-odds')) ));
    }

    protected function prepareForValidation(): void
    {
        $oddId = $this->route('odd');

        if ($oddId && is_string($oddId) && !is_numeric($oddId)) {
            $oddId = Odd::unhashId($oddId);
            $this->merge(['_odd_id' => $oddId]);
        }
    }

    public function rules(): array
    {
        $oddId = $this->input('_odd_id') ?? $this->route('odd');

        return [
            'odd'=> ['required', 'string', Rule::unique('odds', 'odd')->ignore($oddId)->whereNull('deleted_at')]
        ];
    }

    public function messages(): array
    {
        return [
            'odd.required' => 'L\'ODD est obligatoire.',
            'odd.string' => 'L\'ODD doit être une chaîne de caractères.',
            'odd.unique' => 'Cet ODD existe déjà.'
        ];
    }
}
