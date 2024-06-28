<?php

namespace App\Http\Requests\Treasury\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class VoucherSubtypeRequest extends FormRequest {
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
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'La Descripción es obligatoria.',
            'name.max' => 'La Descripción no puede exceder los :max caracteres.',
            'is_active.required' => 'El Estado es obligatorio.',
        ];
    }
}
