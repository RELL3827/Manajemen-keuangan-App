<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VoiceNote;

class VoiceNotePolicy
{
    public function view(User $user, VoiceNote $voiceNote): bool
    {
        return $voiceNote->user_id === $user->id;
    }

    public function delete(User $user, VoiceNote $voiceNote): bool
    {
        return $voiceNote->user_id === $user->id;
    }
}