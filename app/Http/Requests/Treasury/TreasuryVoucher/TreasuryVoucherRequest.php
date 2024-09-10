<?php

namespace App\Http\Requests\Treasury\TreasuryVoucher;

use Illuminate\Foundation\Http\FormRequest;

class TreasuryVoucherRequest extends FormRequest {
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
            'voucherDate' => ['required', 'date', 'before_or_equal:today'],
            'voucherType' => ['required', 'integer', 'exists:voucher_types,id'],
            'voucherSubtype' => ['required', 'integer', 'exists:voucher_subtypes,id'],
            'voucherExpense' => ['required', 'integer'],
            'supplier' => ['required', 'integer', 'exists:suppliers,id'],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    public function messages(): array {
        return [
            'voucherDate.required' => 'La fecha es obligatoria.',
            'voucherType.required' => 'El tipo es obligatorio.',
            'voucherType.exists' => 'El tipo debe existir en el sistema.',
            'voucherSubtype.required' => 'El subtipo es obligatorio.',
            'voucherSubtype.exists' => 'El subtipo debe existir en el sistema.',
            'voucherExpense.required' => 'El gasto es obligatorio.',
            'supplier.required' => 'El proveedor es obligatorio.',
            'supplier.exists' => 'El proveedor debe existir en el sistema.',
            'amount.required' => 'El importe es obligatorio.',
            'amount.min' => 'El importe no puede ser menor a 1.',
            'amount.max' => 'El importe no puede ser mayor a :max.',
        ];
    }
}
