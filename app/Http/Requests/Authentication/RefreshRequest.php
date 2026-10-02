<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Foundation\Http\FormRequest;

class RefreshRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'refresh_token.required' => 'The refresh token is required.',
            'refresh_token.uuid'     => 'The refresh token must be a valid UUID.',
        ];
    }
}
