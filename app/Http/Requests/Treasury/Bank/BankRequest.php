<?php

namespace App\Http\Requests\Treasury\Bank;

use App\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankRequest extends FormRequest {
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
            'name' => ['required', 'string', 'max:100', Rule::unique('banks', 'name')->ignore($this->route('bank'))],
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
            'phone' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'La Descripción es obligatoria.',
            'name.max' => 'La Descripción no puede exceder los :max caracteres.',
            'name.unique' => 'El banco ya se encuentra registrado.',
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
            'phone.required' => 'El teléfono es obligatoria.',
            'phone.max' => 'El teléfono no puede exceder los :max caracteres.',
            'email.required' => 'El Email es obligatoria.',
            'email.max' => 'El Email no puede exceder los :max caracteres.',
        ];
    }

    public function address(): Address {
        return new Address($this->input('address'));
    }
}
