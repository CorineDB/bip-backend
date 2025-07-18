<?php

namespace App\Http\Requests\Trackinfos;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrackInfoRequest extends FormRequest
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