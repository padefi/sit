<?php

namespace App\Http\Requests\Treasury\Supplier;

use App\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name' => ['required', 'string', 'max:100', Rule::unique('suppliers', 'name')->ignore($this->route('supplier'))],
            'businessName' => ['required', 'string', 'max:100'],
            'cuit' => ['required', 'integer', 'min:11111111111', 'max:99999999999', Rule::unique('suppliers', 'cuit')->ignore($this->route('supplier'))],
            'idVC' => ['required', 'integer', 'exists:vat_conditions,id'],
            'idCat' => ['required', 'integer', 'exists:categories,id'],
            'address' => ['required', 'array'],
            'address.street' => ['required', 'string', 'max:100'],
            // 'address.streetNumber' => ['required', 'integer', 'max:99999'],
            'address.city' => ['required', 'string'],
            'address.state' => ['required', 'string'],
            'address.country' => ['required', 'string'],
            'address.osm_id' => ['required', 'integer'],
            'address.latitude' => ['required', 'string', 'max:20'],
            'address.longitude' => ['required', 'string', 'max:20'],
            'address.postalCode' => ['required', 'string', 'max:8'],
            'incomeTaxWithholding' => ['boolean'],
            'socialTax' => ['boolean'],
            'vatTax' => ['boolean'],
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio.',
            'name.max' => 'El nombre del proveedor no puede exceder los :max caracteres.',
            'name.unique' => 'El proveedor ya se encuentra registrado.',
            'businessName.required' => 'El nombre de fantasía del proveedor es obligatorio.',
            'businessName.max' => 'El nombre de fantasía del proveedor no puede exceder los :max caracteres.',
            'cuit.required' => 'El cuit es obligatorio.',
            'cuit.max' => 'El cuit no puede exceder los :max caracteres.',
            'cuit.unique' => 'El cuit ya se encuentra registrado.',
            'idVC.required' => 'La condición de I.V.A. es obligatoria.',
            'idVC.exists' => 'La condición de I.V.A. debe existir en el sistema.',
            'idCat.required' => 'El rubro es obligatorio.',
            'idCat.exists' => 'El rubro es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'address.array' => 'La dirección debe ser un array.',
            'address.street.required' => 'El domicilio es obligatorio.',
            'address.street.max' => 'El domicilio no puede exceder los :max caracteres.',
            // 'address.streetNumber.required' => 'La altura del domicilio es obligatoria.',
            // 'address.streetNumber.max' => 'La altura del domicilio no puede exceder los :max caracteres.',
            'address.city.required' => 'La ciudad es obligatoria.',
            'address.state.required' => 'La provincia es obligatoria.',
            'address.country.required' => 'El país es obligatorio.',
            'address.address.osm_id.required' => 'El id osm es obligatorio.',
            'address.address.latitude.required' => 'La latitud es obligatoria.',
            'address.address.longitude.required' => 'La longitud es obligatoria.',
            'address.postalCode.required' => 'El código postal es obligatorio.',
            'address.postalCode.max' => 'El código postal no puede exceder los :max caracteres.',
            'incomeTaxWithholding.required' => 'La condición de retención de ganancias es obligatoria.',
            'socialTax.required' => 'La condición de retención de suss es obligatoria.',
            'vatTax.required' => 'La condición de retención de I.V.A es obligatoria.',
        ];
    }

    public function address(): Address {
        return new Address($this->input('address'));
    }
}
