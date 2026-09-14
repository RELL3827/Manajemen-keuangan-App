<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VoiceNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'audio_path',
        'transcription',
        'duration',
        'mime_type',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (VoiceNote $voiceNote) {
            if ($voiceNote->audio_path && Storage::disk('local')->exists($voiceNote->audio_path)) {
                Storage::disk('local')->delete($voiceNote->audio_path);
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}