<?php

namespace App\Http\Requests\cibles;

use App\Models\Dgpd;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCibleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd']) || (in_array($user->profilable_type, [Dgpd::class]) && ($user->hasPermissionTo('creer-un-cible') || $user->hasPermissionTo('gerer-les-cibles')) ));
    }

    public function rules(): array
    {
        return [
            'cible'=> ['required', 'string', Rule::unique('cibles', 'cible')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'cible.required' => 'La cible est obligatoire.',
            'cible.string' => 'La cible doit être une chaîne de caractères.',
            'cible.unique' => 'Cette cible existe déjà.'
        ];
    }
}
