<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
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
        'type',
        'amount',
        'currency',
        'description',
        'transaction_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime', // ЗМІНЕНО на datetime для точного часу
    ];

    /**
     * Користувач-власник транзакції.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Категорія транзакції.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Чи є транзакція доходом.
     */
    public function isIncome(): bool
    {
        // Поле type є обов'язковим, тому завжди використовуємо його
        return $this->type === 'income';
    }

    /**
     * Чи є транзакція витратою.
     */
    public function isExpense(): bool
    {
        // Поле type є обов'язковим, тому завжди використовуємо його
        return $this->type === 'expense';
    }

    /**
     * Scope для фільтрації за періодом.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope для фільтрації за категорією.
     */
    public function scopeOfCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope для фільтрації за типом (через категорію).
     */
    public function scopeOfType($query, string $type)
    {
        return $query->whereHas('category', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * Отримати відформатовану суму з символом валюти.
     */
    public function getFormattedAmountAttribute(): string
    {
        $currencyService = app(\App\Services\CurrencyService::class);
        return $currencyService->format($this->amount, $this->currency ?? 'UAH');
    }

    /**
     * Отримати символ валюти.
     */
    public function getCurrencySymbolAttribute(): string
    {
        $currencyService = app(\App\Services\CurrencyService::class);
        return $currencyService->getSymbol($this->currency ?? 'UAH');
    }
}
