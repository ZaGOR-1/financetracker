<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected int $userId;

    protected ?string $dateFrom;

    protected ?string $dateTo;

    protected ?string $type;

    public function __construct(int $userId, ?string $dateFrom = null, ?string $dateTo = null, ?string $type = null)
    {
        $this->userId = $userId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->type = $type;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Transaction::query()
            ->with('category')
            ->where('user_id', $this->userId)
            ->orderBy('transaction_date', 'desc');

        if ($this->dateFrom) {
            $query->where('transaction_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('transaction_date', '<=', $this->dateTo);
        }

        if ($this->type) {
            $query->whereHas('category', fn ($q) => $q->where('type', $this->type));
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Дата',
            'Категорія',
            'Тип',
            'Сума (₴)',
            'Опис',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date,
            $transaction->category->name,
            $transaction->category->type === 'income' ? 'Дохід' : 'Витрата',
            number_format($transaction->amount, 2, '.', ''),
            $transaction->description ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
