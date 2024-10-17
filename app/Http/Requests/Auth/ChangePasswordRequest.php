<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'username' => ['required', 'string'],
            'currentPassword' => ['required', 'string'],
            'newPassword' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:username',
                'regex:/[a-z]/',    // At least one lowercase letter
                'regex:/[A-Z]/',    // At least one uppercase letter
                'regex:/[0-9]/',    // At least one digit
                'regex:/[@$!%*#?&]/',    // At least one special character
            ],
        ];
    }

    public function messages(): array {
        return [
            'username.required' => 'El usuario es obligatorio.',
            'currentPassword.required' => 'La contraseña es obligatoria.',
            'newPassword.required' => 'La contraseña es obligatoria.',
            'newPassword.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'newPassword.confirmed' => 'Las contraseñas no coinciden.',
            'newPassword.different' => 'La contraseña no puede ser igual al nombre de usuario.',
            'newPassword.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula y un número.',
        ];
    }
}
