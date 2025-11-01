<?php

namespace App\Exports;

use App\Models\Budget;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return Budget::query()
            ->with('category')
            ->where('user_id', $this->userId)
            ->where('is_active', true)
            ->orderBy('start_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Категорія',
            'Сума (₴)',
            'Період',
            'Від дати',
            'До дати',
            'Витрачено (₴)',
            'Залишок (₴)',
            'Прогрес (%)',
            'Статус',
        ];
    }

    public function map($budget): array
    {
        return [
            $budget->category ? $budget->category->name : 'Загальний бюджет',
            number_format($budget->amount, 2, '.', ''),
            $this->getPeriodName($budget->period),
            $budget->start_date,
            $budget->end_date,
            number_format($budget->spent, 2, '.', ''),
            number_format($budget->remaining, 2, '.', ''),
            round($budget->percentage, 2),
            $budget->is_over_budget ? 'Перевищено' : ($budget->is_alert_triggered ? 'Попередження' : 'Нормально'),
        ];
    }

    protected function getPeriodName(string $period): string
    {
        return match($period) {
            'daily' => 'Щоденний',
            'weekly' => 'Тижневий',
            'monthly' => 'Місячний',
            'yearly' => 'Річний',
            default => $period,
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
