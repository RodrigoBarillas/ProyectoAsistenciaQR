<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: restringir cuando se agregue autenticación/roles
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_estudiante' => ['required', 'string', 'max:30', 'unique:estudiantes,codigo_estudiante'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'seccion_id' => ['required', 'integer', Rule::exists('secciones', 'id')->where('estado', true)],
            'estado' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'seccion_id.exists' => 'La sección debe existir y estar activa.',
        ];
    }
}
