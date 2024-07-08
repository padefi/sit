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
            'idVC' => ['required', 'integer', 'exists:vat_conditions,id'],
            'idCat' => ['required', 'integer', 'exists:categories,id'],
            'street' => ['required', 'string', 'max:100'],
            'streetNumber' => ['required', 'integer', 'max:5'],
            'city' => ['required', 'integer'],
            'state' => ['required', 'integer'],
            'postalCode' => ['required', 'string', 'max:7'],
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
            'idVC.required' => 'La condición de I.V.A. es obligatoria.',
            'idVC.exists' => 'La condición de I.V.A. es obligatoria.',
            'idCat.required' => 'El rubro es obligatorio.',
            'idCat.exists' => 'El rubro es obligatoria.',
            'street.required' => 'El domicilio es obligatorio.',
            'street.max' => 'El domicilio no puede exceder los :max caracteres.',
            'streetNumber.required' => 'La altura del domicilio es obligatoria.',
            'streetNumber.max' => 'La altura del domicilio no puede exceder los :max caracteres.',
            'city.required' => 'La ciudad es obligatoria.',
            'state.required' => 'La provincia es obligatoria.',
            'postalCode.required' => 'El código postal es obligatorio.',
            'postalCode.max' => 'El código postal no puede exceder los :max caracteres.',
            'incomeTax.required' => 'La condición de ganancias es obligatoria.',
            'socialTax.required' => 'La condición de suss es obligatoria.',
        ];
    }
}
