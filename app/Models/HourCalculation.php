<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hours',
        'hourly_rate',
        'currency',
        'name',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * Relationship: calculation belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Розрахувати денний заробіток
     */
    public function getDailySalaryAttribute(): float
    {
        return round($this->hours * $this->hourly_rate, 2);
    }

    /**
     * Розрахувати тижневий заробіток (5 робочих днів)
     */
    public function getWeeklySalaryAttribute(): float
    {
        return round($this->daily_salary * 5, 2);
    }

    /**
     * Розрахувати місячний заробіток (приблизно 21.67 робочих днів)
     */
    public function getMonthlySalaryAttribute(): float
    {
        return round($this->daily_salary * 21.67, 2);
    }

    /**
     * Розрахувати річний заробіток (260 робочих днів)
     */
    public function getYearlySalaryAttribute(): float
    {
        return round($this->daily_salary * 260, 2);
    }

    /**
     * Отримати символ валюти
     */
    public function getCurrencySymbolAttribute(): string
    {
        return match($this->currency) {
            'UAH' => '₴',
            'USD' => '$',
            'PLN' => 'zł',
            'EUR' => '€',
            default => $this->currency,
        };
    }

    /**
     * Форматований денний заробіток
     */
    public function getFormattedDailySalaryAttribute(): string
    {
        return $this->currency_symbol . number_format($this->daily_salary, 2, '.', ',');
    }

    /**
     * Форматований тижневий заробіток
     */
    public function getFormattedWeeklySalaryAttribute(): string
    {
        return $this->currency_symbol . number_format($this->weekly_salary, 2, '.', ',');
    }

    /**
     * Форматований місячний заробіток
     */
    public function getFormattedMonthlySalaryAttribute(): string
    {
        return $this->currency_symbol . number_format($this->monthly_salary, 2, '.', ',');
    }

    /**
     * Форматований річний заробіток
     */
    public function getFormattedYearlySalaryAttribute(): string
    {
        return $this->currency_symbol . number_format($this->yearly_salary, 2, '.', ',');
    }
}
