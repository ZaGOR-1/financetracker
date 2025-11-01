@extends('layouts.app')

@section('page', 'transactions')

@section('title', 'Додати транзакцію')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Заголовок -->
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <a href="{{ route('transactions.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Додати транзакцію</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Запишіть свій дохід або витрату</p>
            </div>
        </div>
    </div>

    <!-- Форма -->
    <div class="card">
        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-6">
            @csrf

            <!-- Категорія -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Категорія <span class="text-red-500">*</span>
                </label>
                <select 
                    id="category_id" 
                    name="category_id" 
                    required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('category_id') border-red-500 @enderror"
                >
                    <option value="">-- Оберіть категорію --</option>
                    
                    @if($categories->where('type', 'income')->count() > 0)
                    <optgroup label="💰 Доходи">
                        @foreach($categories->where('type', 'income') as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </optgroup>
                    @endif

                    @if($categories->where('type', 'expense')->count() > 0)
                    <optgroup label="💸 Витрати">
                        @foreach($categories->where('type', 'expense') as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Сума -->
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Сума <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        step="0.01"
                        min="0.01"
                        value="{{ old('amount') }}"
                        required
                        placeholder="1000.00"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 pr-12 @error('amount') border-red-500 @enderror"
                    >
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 dark:text-gray-400">₴</span>
                    </div>
                </div>
                @error('amount')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Валюта -->
            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Валюта <span class="text-red-500">*</span>
                </label>
                <select 
                    id="currency" 
                    name="currency" 
                    required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('currency') border-red-500 @enderror"
                >
                    @foreach(config('currencies.supported') as $code => $currencyData)
                        <option value="{{ $code }}" {{ old('currency', auth()->user()->default_currency ?? 'UAH') === $code ? 'selected' : '' }}>
                            {{ $currencyData['symbol'] }} {{ $currencyData['name'] }} ({{ $code }})
                        </option>
                    @endforeach
                </select>
                @error('currency')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Дата і час транзакції -->
            <div>
                <label for="transaction_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Дата і час <span class="text-red-500">*</span>
                </label>
                <input 
                    type="datetime-local" 
                    id="transaction_date" 
                    name="transaction_date" 
                    value="{{ old('transaction_date', date('Y-m-d\TH:i')) }}"
                    required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('transaction_date') border-red-500 @enderror"
                >
                @error('transaction_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Опис -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Опис
                    <span class="text-gray-500 text-xs">(необов'язково)</span>
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="3"
                    placeholder="Додайте детальний опис транзакції..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('description') border-red-500 @enderror"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    💡 Наприклад: "Продукти в Сільпо", "Зарплата за червень", "Ремонт автомобіля"
                </p>
            </div>

            <!-- Інформаційний блок -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                            Швидкі поради
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Додавайте транзакції одразу після здійснення - так ви нічого не забудете</li>
                                <li>Використовуйте детальні описи для легшого пошуку в майбутньому</li>
                                <li>Перевіряйте правильність категорії - це впливає на статистику</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('transactions.index') }}" class="btn-secondary">
                    Скасувати
                </a>
                <button type="submit" class="btn-primary">
                    💾 Зберегти транзакцію
                </button>
            </div>
        </form>
    </div>

    <!-- Швидкі кнопки (часто використовувані категорії) -->
    @php
        $quickCategories = $categories->whereIn('name', ['Зарплата', 'Їжа', 'Транспорт', 'Розваги']);
    @endphp
    
    @if($quickCategories->count() > 0)
    <div class="mt-6 card bg-gray-50 dark:bg-gray-800/50">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">⚡ Швидкий доступ</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            @foreach($quickCategories as $quickCategory)
            <button 
                type="button"
                onclick="document.getElementById('category_id').value='{{ $quickCategory->id }}'"
                class="p-3 text-left rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 transition"
            >
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $quickCategory->color }}"></span>
                    <span class="text-sm text-gray-900 dark:text-white">{{ $quickCategory->name }}</span>
                </div>
            </button>
            @endforeach
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Клікніть для швидкого вибору популярної категорії</p>
    </div>
    @endif
</div>
@endsection
