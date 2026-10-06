<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_estudiante' => ['required', 'string', 'max:30', 'unique:estudiantes,codigo_estudiante'],
            'nombres'           => ['required', 'string', 'max:100'],
            'apellidos'         => ['required', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'seccion_id'        => ['required', 'integer', Rule::exists('secciones', 'id')->where('estado', true)],
            'estado'            => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'El correo electrónico es requerido.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'email.unique'      => 'Este correo ya está registrado en el sistema.',
            'seccion_id.exists' => 'La sección debe existir y estar activa.',
        ];
    }
}
