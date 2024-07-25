<?php

namespace App\Http\Requests\Treasury\Taxes;

use Illuminate\Foundation\Http\FormRequest;

class VatTaxWithholdingRequest extends FormRequest {
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
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'minAmount' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'fixedAmount' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'startAt' => ['required', 'date', 'before:endAt'],
            'endAt' => ['required', 'date', 'after:startAt'],
        ];
    }

    public function messages(): array {
        return [
            'rate.required' => 'El Porcentaje es obligatorio.',
            'rate.min' => 'El Porcentaje no puede ser menor a 0.',
            'rate.max' => 'El Porcentaje no puede ser mayor a 100.',
            'minAmount.required' => 'El monto mínimo es obligatorio.',
            'minAmount.min' => 'El monto mínimo no puede ser menor a 0.',
            'minAmount.max' => 'El monto mínimo no puede ser mayor a 99999999.',
            'fixedAmount.required' => 'El monto fijo es obligatorio.',
            'fixedAmount.min' => 'El monto fijo no puede ser menor a 0.',
            'fixedAmount.max' => 'El monto fijo no puede ser mayor a 99999999.',
            'startAt.required' => 'La fecha de inicio es obligatoria.',
            'startAt.before' => 'La fecha de inicio no puede ser posterior a la fecha de finalización.',
            'endAt.required' => 'La fecha de finalización es obligatoria.',
            'endAt.after' => 'La fecha de finalización no puede ser anterior a la fecha de inicio.',
        ];
    }
}
