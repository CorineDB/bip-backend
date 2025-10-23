<?php

namespace App\Http\Requests\fichiers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFichierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && ($user->hasPermissionTo('televerser-un-fichier'));
    }

    public function rules(): array
    {
        return [
            // TODO: add validation rules
        ];
    }
}
