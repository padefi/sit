<?php

namespace App\Http\Requests\Treasury\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest {
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
            'businessName' => ['required', 'string', 'max:100'],
            'cuit' => ['required', 'ingeger', 'max:11'],
            'idTC' => ['required', 'integer', 'exists:tax_conditions,id'],
            'address' => ['required', 'string', 'max:100'],
            'addressNumber' => ['required', 'integer'],
            'incomeTax' => ['boolean'],
            'socialTax' => ['boolean'],
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio.',
            'name.max' => 'El nombre del proveedor no puede exceder los :max caracteres.',
            'businessName.required' => 'El nombre de fantasía del proveedor es obligatorio.',
            'businessName.max' => 'El nombre de fantasía del proveedor no puede exceder los :max caracteres.',
            'cuit.required' => 'El cuit es obligatorio.',
            'cuit.max' => 'El cuit no puede exceder los :max caracteres.',
            'idTC.required' => 'El tipo de proveedor es obligatorio.',
            'idTC.exists' => 'El tipo de proveedor es obligatorio.',
            'address.required' => 'El domicilio es obligatorio.',
            'address.max' => 'El domicilio no puede exceder los :max caracteres.',
            'addressNumber.required' => 'La altura del domicilio es obligatoria.',
            'addressNumber.max' => 'La altura del domicilio no puede exceder los :max caracteres.',
            'incomeTax.required' => 'La condición de ganancias es obligatoria.',
            'socialTax.required' => 'La condición de suss es obligatoria.',
        ];
    }
}
