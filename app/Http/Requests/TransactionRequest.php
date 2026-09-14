<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
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
            'type' => ['required', Rule::in(['income', 'expense'])],
            'account_id' => ['required', 'exists:accounts,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', Rule::in(['manual', 'voice'])],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis transaksi wajib diisi.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.min' => 'Nominal minimal Rp1.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'account_id.required' => 'Pilih akun/wallet.',
            'category_id.required' => 'Pilih kategori.',
        ];
    }
}