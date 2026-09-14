<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParseVoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transcript' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'transcript.required' => 'Transkripsi kosong.',
        ];
    }
}