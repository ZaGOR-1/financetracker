@extends('layouts.app')

@section('page', 'transactions')

@section('title', 'Редагувати транзакцію')

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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Редагувати транзакцію</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ $transaction->transaction_date->format('d.m.Y') }} • 
                    {{ number_format($transaction->amount, 2, ',', ' ') }} ₴ • 
                    {{ $transaction->category->name }}
                </p>
            </div>
        </div>
    </div>

    <!-- Форма -->
    <div class="card">
        <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="space-y-6">
            @csrf
            @method('PUT')

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
                    
                    <optgroup label="💰 Доходи">
                        @foreach(\App\Models\Category::where('type', 'income')->where(function($q) {
                            $q->whereNull('user_id')->orWhere('user_id', auth()->id());
                        })->orderBy('name')->get() as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="💸 Витрати">
                        @foreach(\App\Models\Category::where('type', 'expense')->where(function($q) {
                            $q->whereNull('user_id')->orWhere('user_id', auth()->id());
                        })->orderBy('name')->get() as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </optgroup>
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
                        value="{{ old('amount', $transaction->amount) }}"
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
                        <option value="{{ $code }}" {{ old('currency', $transaction->currency ?? 'UAH') === $code ? 'selected' : '' }}>
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
                    value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d\TH:i')) }}"
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
                >{{ old('description', $transaction->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Інформація про транзакцію -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">📊 Деталі транзакції</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Тип</p>
                        <p class="text-gray-900 dark:text-white font-medium">
                            @if($transaction->category->type === 'income')
                                💰 Дохід
                            @else
                                💸 Витрата
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Створено</p>
                        <p class="text-gray-900 dark:text-white">{{ $transaction->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    @if($transaction->created_at != $transaction->updated_at)
                    <div class="col-span-2">
                        <p class="text-gray-500 dark:text-gray-400">Останнє редагування</p>
                        <p class="text-gray-900 dark:text-white">{{ $transaction->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary text-red-600 hover:text-red-700 dark:text-red-400">
                        🗑️ Видалити транзакцію
                    </button>
                </form>

                <div class="flex space-x-3">
                    <a href="{{ route('transactions.index') }}" class="btn-secondary">
                        Скасувати
                    </a>
                    <button type="submit" class="btn-primary">
                        💾 Зберегти зміни
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
