@extends('layouts.app')

@section('page', 'transactions')

@section('title', 'Транзакції')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Транзакції</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Управління доходами та витратами</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('export.transactions', request()->only(['date_from', 'date_to', 'type'])) }}" 
           class="btn-secondary flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Експорт
        </a>
        <a href="{{ route('transactions.create') }}" class="btn-primary">
            + Додати транзакцію
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-6">
    <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Від дати</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">До дати</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Тип</label>
            <select name="type"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Всі</option>
                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Доходи</option>
                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Витрати</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full">Фільтрувати</button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="card">
    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="hidden px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700 dark:text-gray-300">
                Вибрано: <span id="selectedCount" class="font-semibold">0</span>
            </span>
            <button type="button" onclick="bulkDelete()" class="btn-secondary text-red-600 hover:text-red-700 dark:text-red-400">
                🗑️ Видалити вибрані
            </button>
        </div>
    </div>

    <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" 
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                    </th>
                    <th class="px-6 py-3">Дата і час</th>
                    <th class="px-6 py-3">Категорія</th>
                    <th class="px-6 py-3">Опис</th>
                    <th class="px-6 py-3">Сума</th>
                    <th class="px-6 py-3">Дії</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4">
                        <input type="checkbox" name="transaction_ids[]" value="{{ $transaction->id }}" 
                               class="transaction-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600"
                               onclick="updateBulkActions()">
                    </td>
                    <td class="px-6 py-4">{{ $transaction->transaction_date->format('d.m.Y H:i:s') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $transaction->category->color }}"></span>
                            {{ $transaction->category->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ $transaction->description ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="font-semibold {{ $transaction->category->type == 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->category->type == 'income' ? '+' : '-' }}{{ $transaction->formatted_amount }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('transactions.edit', $transaction) }}" class="text-blue-600 hover:underline">Редагувати</a>
                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Видалити</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        Немає транзакцій. <a href="{{ route('transactions.create') }}" class="text-primary-600 hover:underline">Додати першу</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

<!-- Hidden form for bulk delete -->
<form id="bulkDeleteForm" method="POST" action="{{ route('transactions.bulk-destroy') }}" style="display: none;">
    @csrf
    @method('DELETE')
    <div id="bulkDeleteIds"></div>
</form>

@endsection

@push('scripts')
<script>
    // Toggle select all checkboxes
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.transaction-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkActions();
    }

    // Update bulk actions bar visibility and count
    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.transaction-checkbox:checked');
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCount = document.getElementById('selectedCount');
        const selectAll = document.getElementById('selectAll');
        
        const count = checkboxes.length;
        selectedCount.textContent = count;
        
        if (count > 0) {
            bulkActionsBar.classList.remove('hidden');
        } else {
            bulkActionsBar.classList.add('hidden');
        }
        
        // Update "select all" checkbox state
        const allCheckboxes = document.querySelectorAll('.transaction-checkbox');
        selectAll.checked = count === allCheckboxes.length && count > 0;
        selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
    }

    // Bulk delete selected transactions
    function bulkDelete() {
        const checkboxes = document.querySelectorAll('.transaction-checkbox:checked');
        
        if (checkboxes.length === 0) {
            return;
        }
        
        const form = document.getElementById('bulkDeleteForm');
        const idsContainer = document.getElementById('bulkDeleteIds');
        
        // Clear previous inputs
        idsContainer.innerHTML = '';
        
        // Add hidden inputs for each selected transaction
        checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'transaction_ids[]';
            input.value = checkbox.value;
            idsContainer.appendChild(input);
        });
        
        // Submit form
        form.submit();
    }
</script>
@endpush
