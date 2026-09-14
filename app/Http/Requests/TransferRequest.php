<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
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
            'from_account_id' => ['required', 'different:to_account_id', 'exists:accounts,id'],
            'to_account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_account_id.required' => 'Pilih akun asal.',
            'to_account_id.required' => 'Pilih akun tujuan.',
            'from_account_id.different' => 'Akun sumber dan tujuan harus berbeda.',
            'amount.required' => 'Nominal transfer wajib diisi.',
        ];
    }
}