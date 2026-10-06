<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegistrarAsistenciaRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
        'qr_token' => ['required', 'string', 'uuid', 'exists:estudiantes,qr_token,estado,1'],
    ];
    }

    public function messages(): array
    {
        return [
        'qr_token.required' => 'El código QR es requerido.',
        'qr_token.uuid'     => 'El código QR no es válido.',
        'qr_token.exists'   => 'El código QR no corresponde a un estudiante activo.',
    ];
    }

    protected function failedValidation(Validator $validator)
    {
    throw new HttpResponseException(response()->json([
        'success' => false,
        'message' => $validator->errors()->first(),
    ], 422));
    }
}
