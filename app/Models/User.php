<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use \Illuminate\Auth\MustVerifyEmail;

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            $user->voiceNotes()->get()->each->delete();
        });
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'currency',
        'notif_budget',
        'notif_reminder',
        'notif_weekly',
        'notif_monthly',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notif_budget' => 'boolean',
            'notif_reminder' => 'boolean',
            'notif_weekly' => 'boolean',
            'notif_monthly' => 'boolean',
        ];
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function accounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transfers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function budgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function voiceNotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VoiceNote::class);
    }

    public function notificationsUser(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function getTotalBalanceAttribute(): float
    {
        return (float) $this->accounts()->sum('balance');
    }

    protected ?int $cachedUnreadCount = null;

    public function getUnreadNotificationsCountAttribute(): int
    {
        if ($this->cachedUnreadCount !== null) {
            return $this->cachedUnreadCount;
        }

        return $this->cachedUnreadCount = $this->notificationsUser()->unread()->count();
    }
}