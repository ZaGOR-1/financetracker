<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'period',
        'start_date',
        'end_date',
        'alert_threshold',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'alert_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Користувач-власник бюджету.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Категорія бюджету (nullable для загальних бюджетів).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Отримати суму витрат за період бюджету.
     */
    public function getSpentAttribute(): float
    {
        $query = Transaction::where('user_id', $this->user_id)
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->whereHas('category', fn($q) => $q->where('type', 'expense'));

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Отримати залишок бюджету.
     */
    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->spent);
    }

    /**
     * Отримати відсоток використання бюджету.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->amount == 0) {
            return 0;
        }
        return round(($this->spent / $this->amount) * 100, 2);
    }

    /**
     * Чи перевищено бюджет.
     */
    public function isOverBudget(): bool
    {
        return $this->spent > $this->amount;
    }

    /**
     * Чи досягнуто порогу попередження.
     */
    public function isAlertTriggered(): bool
    {
        return $this->percentage >= $this->alert_threshold;
    }

    /**
     * Scope для активних бюджетів.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для поточних бюджетів (дата зараз між start і end).
     */
    public function scopeCurrent($query)
    {
        $now = now()->toDateString();
        return $query->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }
}
