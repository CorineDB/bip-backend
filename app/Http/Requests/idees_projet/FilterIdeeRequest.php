<?php

namespace App\Http\Requests\idees_projet;

use App\Enums\StatutIdee;
use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterIdeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array($user->type, ['super-admin', 'dgpd', 'dpaf', 'organisation']) || (in_array($user->profilable_type, [Dgpd::class, Dpaf::class, Organisation::class]))) ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'statut' => ['nullable', Rule::in(StatutIdee::values())],
        ];
    }
}
