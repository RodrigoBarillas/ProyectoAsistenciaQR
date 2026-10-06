<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
            // The QR payload is a Crypt::encryptString() string — any non-empty
            // string is accepted here; decryption failure is handled in the controller.
            'qr_token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'qr_token.required' => 'El código QR es requerido.',
            'qr_token.string'   => 'El código QR no tiene un formato válido.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
