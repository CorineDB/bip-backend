<?php

namespace App\Http\Requests\odds;

use App\Models\Dgpd;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOddRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('creer-un-odd') || $user->hasPermissionTo('gerer-les-odds')) ));
    }

    public function rules(): array
    {
        return [
            'odd'=> ['required', 'string', Rule::unique('odds', 'odd')->whereNull('deleted_at')]
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
