<?php

namespace App\Http\Requests\Treasury\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class VoidedVoucherRequest extends FormRequest {
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
            'idVoucher' => ['required', 'integer', 'exists:vouchers,id'],
            'notes' => ['required', 'string', 'max:250'],
        ];
    }

    public function messages(): array {
        return [
            'idVoucher.required' => 'El comprobante es obligatorio.',
            'idVoucher.exists' => 'El comprobante debe existir en el sistema.',
            'notes.required' => 'El motivo de anulación es obligatoria.',
            'notes.max' => 'El motivo de anulación no puede exceder los :max caracteres.',
        ];
    }
}
