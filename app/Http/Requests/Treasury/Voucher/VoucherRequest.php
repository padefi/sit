<?php

namespace App\Http\Requests\Treasury\Voucher;

use App\VoucherItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VoucherRequest extends FormRequest {
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
            'voucherType' => ['required', 'integer', 'exists:voucher_types,id'],
            'voucherSubtype' => ['required', 'integer', 'exists:voucher_subtypes,id'],
            'voucherExpense' => ['required', 'integer'],
            'invoiceType' => ['required', 'integer', 'exists:invoice_types,id'],
            'invoiceTypeCode' => ['required', 'integer', 'exists:invoice_type_codes,id'],
            'pointOfNumber' => ['required', 'string', 'min: 1', 'max:5'],
            'invoiceNumber' => ['required', 'string', 'min: 1', 'max:8'],
            'invoiceDate' => ['required', 'date'],
            'invoiceDueDate' => ['required', 'date', 'after_or_equal:invoiceDate'],
            'payCondition' => ['required', 'integer', 'exists:pay_conditions,id'],
            'voucherItems' => ['required', 'array', 'min:1'],
            'voucherItems.*.description' => ['required', 'string', 'max:100'],
            'voucherItems.*.vat' => ['required', 'integer', 'exists:vat_rates,id'],
            'voucherItems.*.amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'voucherItems.*.subtotalAmount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'totalAmount' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    protected function withValidator(Validator $validator) {
        $validator->after(function ($validator) {
            foreach ($this->input('voucherItems', []) as $index => $item) {
                $amount = $item['amount'] ?? null;
                $subtotalAmount = $item['subtotalAmount'] ?? null;

                if ($amount !== null && $subtotalAmount !== null && $amount > $subtotalAmount) {
                    $validator->errors()->add("voucherItems.$index.amount", 'El importe no puede ser mayor al subtotal.');
                }
            }
        });
    }

    public function messages(): array {
        return [
            'voucherType.required' => 'El tipo es obligatorio.',
            'voucherType.exists' => 'El tipo debe existir en el sistema.',
            'voucherSubtype.required' => 'El subtipo es obligatorio.',
            'voucherSubtype.exists' => 'El subtipo debe existir en el sistema.',
            'voucherExpense.required' => 'El gasto es obligatorio.',
            'invoiceType.required' => 'El tipo de comprobante es obligatorio.',
            'invoiceType.exists' => 'El tipo de comprobante debe existir en el sistema.',
            'invoiceTypeCode.required' => 'El código del tipo de comprobante es obligatorio.',
            'invoiceTypeCode.exists' => 'El código del tipo de comprobante debe existir en el sistema.',
            'pointOfNumber.required' => 'El pto. de venta es obligatorio.',
            'pointOfNumber.min' => 'El pto. de venta no puede ser menor a :min.',
            'pointOfNumber.max' => 'El pto. de venta no puede ser mayor a :max.',
            'invoiceNumber.required' => 'El n° del comprobante es obligatorio.',
            'invoiceNumber.min' => 'El n° del comprobante no puede ser menor a :min.',
            'invoiceNumber.max' => 'El n° del comprobante no puede ser mayor a :max.',
            'invoiceDate.required' => 'La fecha de emisión es obligatoria.',
            'invoiceDueDate.required' => 'La fecha de vencimiento es obligatoria.',
            'invoiceDueDate.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a la fecha de emisión.',
            'idPC.required' => 'La condición de pago es obligatoria.',
            'idPC.exists' => 'La condición de pago debe existir en el sistema.',
            'voucherItems.required' => 'Debes incluir al menos un ítem en el comprobante.',
            'voucherItems.array' => 'El campo de ítems debe ser un array.',
            'voucherItems.min' => 'El comprobante debe tener al menos un ítem.',
            'voucherItems.*.required' => 'El item del comprobante es obligatorio.',
            'voucherItems.*.array' => 'El item debe ser un array.',
            'voucherItems.*.description.required' => 'La descripción del item es obligatoria.',
            'voucherItems.*.description.max' => 'La descripción no puede exceder los :max caracteres.',
            'voucherItems.*.amount.required' => 'El importe del item es obligatorio.',
            'voucherItems.*.amount.min' => 'El importe no puede ser menor a 1.',
            'voucherItems.*.amount.max' => 'El importe no puede ser mayor a :max.',
            'voucherItems.*.vat.required' => 'El proncentaje de I.V.A. del item es obligatorio.',
            'voucherItems.*.vat.exists' => 'El proncentaje de I.V.A. del item es obligatorio.',
            'voucherItems.*.subtotalAmount.required' => 'El subtotal del item es obligatorio.',
            'voucherItems.*.subtotalAmount.max' => 'El subtotal no puede ser mayor a :max.',
        ];
    }

    public function voucherItem(): VoucherItem {
        return new VoucherItem($this->input('voucherItems'));
    }
}
