<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: restringir cuando se agregue autenticación/roles
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grados', 'nombre')->ignore($this->grado),
            ],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
