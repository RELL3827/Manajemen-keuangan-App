<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('initial_balance')) {
            $this->merge([
                'initial_balance' => (int) preg_replace('/\D/', '', (string) $this->input('initial_balance')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'type' => ['required', Rule::in(['cash', 'bank', 'ewallet', 'other'])],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:12'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama akun wajib diisi.',
            'type.required' => 'Tipe akun wajib diisi.',
        ];
    }
}