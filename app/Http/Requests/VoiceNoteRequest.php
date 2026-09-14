<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoiceNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'exists:transactions,id'],
            'audio' => ['required', 'file', 'mimetypes:audio/webm,audio/mp4,audio/mpeg,audio/wav,video/webm,application/octet-stream', 'max:20000'],
            'transcription' => ['nullable', 'string', 'max:4000'],
            'duration' => ['nullable', 'integer', 'max:7200'],
        ];
    }

    public function messages(): array
    {
        return [
            'audio.required' => 'File audio belum diunggah.',
            'audio.max' => 'Ukuran audio maksimal 20MB.',
        ];
    }
}