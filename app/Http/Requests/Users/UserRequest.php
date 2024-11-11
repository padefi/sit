<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest {
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
            'surname' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'max:100', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'reset_password' => ['boolean'],
        ];
    }

    public function messages(): array {
        return [
            'surname.required' => 'El Apellido es obligatorio.',
            'surname.max' => 'El Apellido no puede exceder los :max caracteres.',
            'name.required' => 'El Nombre es obligatorio.',
            'name.max' => 'El Apellido no puede exceder los :max caracteres.',
            'email.required' => 'El Email es obligatorio.',
            'email.max' => 'El Apellido no puede exceder los :max caracteres.',
            'email.unique' => 'El Email ya se encuentra registrado.',
            'role.required' => 'El Rol es obligatorio.',
            'role.exists' => 'El Rol es obligatorio.',
        ];
    }
}
