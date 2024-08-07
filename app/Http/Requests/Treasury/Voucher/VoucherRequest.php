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
            'idType' => ['required', 'integer', 'exists:voucher_types,id'],
            'idSubtype' => ['required', 'integer', 'exists:voucher_subtypes,id'],
            'idExpense' => ['required', 'integer'],
            'idIT' => ['required', 'integer', 'exists:invoice_types,id'],
            'idITCode' => ['required', 'integer', 'exists:invoice_type_codes,id'],
            'pointOfNumber' => ['required', 'integer', 'min: 1', 'max:99999'],
            'invoiceNumber' => ['required', 'integer', 'min: 1', 'max:99999999'],
            'invoiceDate' => ['required', 'date', 'before:invoicePaymentDate'],
            'invoicePaymentDate' => ['required', 'date', 'after:invoiceDate'],
            'idPC' => ['required', 'integer', 'exists:pay_conditions,id'],
            'voucherItems' => ['array'],
            'voucherItems.description' => ['required', 'string', 'max:100'],
            'voucherItems.vat' => ['required', 'integer', 'exists:vat_rates,id'],
            'voucherItems.amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'voucherItems.subtotalAmount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'totalAmount' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    protected function withValidator(Validator $validator) {
        $validator->after(function ($validator) {
            $amount = $this->input('amount');
            $subtotalAmount = $this->input('subtotalAmount');

            if ($amount !== null && $subtotalAmount !== null && $amount > $subtotalAmount) {
                $validator->errors()->add('amount', 'El importe no puede ser mayor al subtotal.');
            }
        });
    }

    public function messages(): array {
        return [
            'idType.required' => 'El tipo es obligatorio.',
            'idType.exists' => 'El tipo debe existir en el sistema.',
            'idSubtype.required' => 'El subtipo es obligatorio.',
            'idSubtype.exists' => 'El subtipo debe existir en el sistema.',
            'idExpense.required' => 'El gasto es obligatorio.',
            'idIT.required' => 'El tipo de comprobante es obligatorio.',
            'idIT.exists' => 'El tipo de comprobante debe existir en el sistema.',
            'idITCode.required' => 'El código del tipo de comprobante es obligatorio.',
            'idITCode.exists' => 'El código del tipo de comprobante debe existir en el sistema.',
            'pointOfNumber.required' => 'El pto. de venta es obligatorio.',
            'pointOfNumber.min' => 'El pto. de venta no puede ser menor a :min.',
            'pointOfNumber.max' => 'El pto. de venta no puede ser mayor a :max.',
            'invoiceNumber.required' => 'El n° del comprobante es obligatorio.',
            'invoiceNumber.min' => 'El n° del comprobante no puede ser menor a :min.',
            'invoiceNumber.max' => 'El n° del comprobante no puede ser mayor a :max.',
            'invoiceDate.required' => 'La fecha de emisión es obligatoria.',
            'invoiceDate.before' => 'La fecha de emisión no puede ser posterior a la fecha de vencimiento.',
            'invoicePaymentDate.required' => 'La fecha de vencimiento es obligatoria.',
            'invoicePaymentDate.after' => 'La fecha de vencimiento no puede ser anterior a la fecha de emisión.',
            'idPC.required' => 'La condición de pago es obligatoria.',
            'idPC.exists' => 'La condición de pago debe existir en el sistema.',
            'voucherItems.required' => 'El item del comprobante es obligatorio.',
            'voucherItems.array' => 'El item debe ser un array.',
            'voucherItems.description.required' => 'La descripción del item es obligatoria.',
            'voucherItems.description.max' => 'La descripción no puede exceder los :max caracteres.',
            'voucherItems.amount.required' => 'El importe del item es obligatorio.',
            'voucherItems.amount.min' => 'El importe no puede ser menor a 1.',
            'voucherItems.amount.max' => 'El importe no puede ser mayor a :max.',
            'voucherItems.vat.required' => 'El proncentaje de I.V.A. del item es obligatorio.',
            'voucherItems.vat.exists' => 'El proncentaje de I.V.A. del item es obligatorio.',
            'voucherItems.subtotalAmount.required' => 'El subtotal del item es obligatorio.',
            'voucherItems.subtotalAmount.max' => 'El subtotal no puede ser mayor a :max.',
        ];
    }

    public function voucherItem(): VoucherItem {
        return new VoucherItem($this->input('voucherItems'));
    }
}
