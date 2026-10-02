<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: restringir cuando se agregue autenticación/roles
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_estudiante' => [
                'required',
                'string',
                'max:30',
                Rule::unique('estudiantes', 'codigo_estudiante')->ignore($this->route('estudiante')),
            ],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
