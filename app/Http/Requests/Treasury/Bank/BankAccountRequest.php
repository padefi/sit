<?php

namespace App\Http\Requests\Treasury\Bank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'accountNumber' => ['required', 'string', 'max:10', Rule::unique('bank_accounts', 'accountNumber')->where(function ($query) {
                return $query->where('idBank', request('idBank'))
                    ->where('idAT', request('idAT'));
            })->ignore($this->route('bankAccount'))],
            'cbu' => ['required', 'string', 'max:22', Rule::unique('bank_accounts', 'cbu')->ignore($this->route('bankAccount'))],
            'alias' => ['required', 'string', 'max:20', Rule::unique('bank_accounts', 'alias')->ignore($this->route('bankAccount'))],
            'idBank' => ['required', 'integer', 'exists:banks,id'],
            'idAT' => ['required', 'integer', 'exists:bank_account_types,id'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array {
        return [
            'accountNumber.required' => 'El Nº de Cuenta bancaria es obligatoria.',
            'accountNumber.max' => 'El Nº de Cuenta no puede exceder los :max caracteres.',
            'accountNumber.unique' => 'El Nº de Cuenta ya se encuentra ingresada.',
            'cbu.required' => 'El CBU es obligatoria.',
            'cbu.max' => 'El CBU no puede exceder los :max caracteres.',
            'cbu.unique' => 'El CBU ya existe.',
            'alias.required' => 'El Alias es obligatoria.',
            'alias.max' => 'El Alias no puede exceder los :max caracteres.',
            'alias.unique' => 'El Alias ya existe.',
            'idBank.required' => 'El Banco es obligatorio.',
            'idBank.exists' => 'El Banco es obligatorio.',
            'idAT.required' => 'El Tipo de Cuenta es obligatoria.',
            'idAT.exists' => 'El Tipo de Cuenta es obligatoria.',
            'status.required' => 'El Estado es obligatorio.',
        ];
    }
}
