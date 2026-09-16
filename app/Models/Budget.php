<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'period',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected ?float $cachedSpent = null;

    public function setCachedSpent(float $amount): static
    {
        $this->cachedSpent = $amount;
        return $this;
    }

    public function spentAmount(): float
    {
        if ($this->cachedSpent !== null) {
            return $this->cachedSpent;
        }

        return $this->cachedSpent = (float) Transaction::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->sum('amount');
    }

    public function getProgressAttribute(): int
    {
        $ratio = (float) $this->amount > 0 ? $this->spentAmount() / (float) $this->amount : 0;

        return (int) min(100, round($ratio * 100));
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - $this->spentAmount());
    }
}