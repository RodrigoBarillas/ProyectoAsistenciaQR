<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeccionRequest extends FormRequest
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
                Rule::unique('secciones')->where(fn ($query) => $query->where('grado_id', $this->grado_id)),
            ],
            'grado_id' => ['required', 'integer', 'exists:grados,id'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
