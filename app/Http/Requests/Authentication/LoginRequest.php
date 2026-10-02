<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'The email field is required.',
            'email.email'       => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'password.min'      => 'The password must be at least 6 characters.',
        ];
    }
}
