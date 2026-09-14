<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $this->merge([
                'amount' => (int) preg_replace('/\D/', '', (string) $this->input('amount')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'period' => ['required', 'in:monthly,weekly'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Pilih kategori.',
            'amount.required' => 'Nominal budget wajib diisi.',
            'period.required' => 'Periode budget wajib diisi.',
        ];
    }
}