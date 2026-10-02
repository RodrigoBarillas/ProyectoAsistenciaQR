<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeccionRequest extends FormRequest
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
                'max:20',
                Rule::unique('secciones')
                    ->where(fn ($query) => $query->where('grado_id', $this->grado_id))
                    ->ignore($this->route('seccion')),
            ],
            'grado_id' => ['required', 'integer', Rule::exists('grados', 'id')->where('estado', true)],
            'estado' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'grado_id.exists' => 'El grado debe existir y estar activo.',
        ];
    }
}
