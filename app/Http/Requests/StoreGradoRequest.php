<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: restringir cuando se agregue autenticación/roles
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:50', 'unique:grados,nombre'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
