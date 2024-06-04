<?php

namespace App\Http\Requests\Treasury;

use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'La Descripción es obligatoria.',
            'name.max' => 'La Descripción no puede exceder los :max caracteres.',
            'address.required' => 'La Dirección es obligatoria.',
            'address.max' => 'La Dirección no puede exceder los :max caracteres.',
            'phone.required' => 'El teléfono es obligatoria.',
            'phone.max' => 'El teléfono no puede exceder los :max caracteres.',
            'email.required' => 'El Email es obligatoria.',
            'email.max' => 'El Email no puede exceder los :max caracteres.',
        ];
    }
}
