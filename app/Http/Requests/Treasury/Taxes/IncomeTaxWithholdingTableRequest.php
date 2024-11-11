<?php

namespace App\Http\Requests\Treasury\Taxes;

use Illuminate\Foundation\Http\FormRequest;

class IncomeTaxWithholdingTableRequest extends FormRequest {
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
            'idCat' => ['required', 'exists:categories,id'],
            'table' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array {
        return [
            'idCat.required' => 'El rubro es obligatorio.',
            'idCat.exists' => 'El rubro es obligatorio.',
            'table.required' => 'La tabla es obligatori.',
            'table.max' => 'La tabla no puede exceder los :max caracteres.',
        ];
    }
}
