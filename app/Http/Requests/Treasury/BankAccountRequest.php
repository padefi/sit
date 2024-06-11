<?php

namespace App\Http\Requests\Treasury;;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest {
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
            'accountNumber' => ['required', 'string', 'max:10'],
            'cbu' => ['required', 'string', 'max:22'],
            'alias' => ['required', 'string', 'max:20'],
            'idBank' => ['required', 'integer', 'exists:banks,id'],
            'idAT' => ['required', 'integer', 'exists:bank_account_types,id'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array {
        return [
            'accountNumber.required' => 'El Nº de Cuenta bancaria es obligatoria.',
            'accountNumber.max' => 'La Nº de Cuenta no puede exceder los :max caracteres.',
            'cbu.required' => 'El CBU es obligatoria.',
            'cbu.max' => 'El CBU no puede exceder los :max caracteres.',
            'alias.required' => 'El Alias es obligatoria.',
            'alias.max' => 'El Alias no puede exceder los :max caracteres.',
            'idBank.required' => 'El Banco es obligatorio.',
            'idBank.exists' => 'El Banco es obligatorio.',
            'idAT.required' => 'El Tipo de Cuenta es obligatoria.',
            'idAT.exists' => 'El Tipo de Cuenta es obligatoria.',
            'status.required' => 'El Estado es obligatorio.',
        ];
    }
}
