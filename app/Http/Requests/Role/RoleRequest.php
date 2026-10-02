<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'nombre'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'nombre')->ignore($roleId),
            ],
            'descripcion'   => ['nullable', 'string', 'max:150'],
            'estado'        => ['boolean'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'        => 'The role name is required.',
            'nombre.unique'          => 'A role with this name already exists.',
            'nombre.max'             => 'The role name must not exceed 50 characters.',
            'permissions.array'      => 'Permissions must be an array.',
            'permissions.*.integer'  => 'Each permission must be an integer ID.',
            'permissions.*.exists'   => 'One or more permission IDs do not exist.',
        ];
    }
}

