@extends('layouts.app')

@section('page', 'budgets')

@section('title', 'Редагувати бюджет')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Заголовок -->
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <a href="{{ route('budgets.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Редагувати бюджет</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    @if($budget->category)
                        {{ $budget->category->name }} • {{ ucfirst($budget->period) }}
                    @else
                        Загальний бюджет • {{ ucfirst($budget->period) }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Статистика поточного бюджету -->
    <div class="card mb-6 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 border-primary-200 dark:border-primary-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Поточний стан бюджету</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Бюджет</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($budget->amount, 2, ',', ' ') }} ₴</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Витрачено</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($budget->spent, 2, ',', ' ') }} ₴</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Залишок</p>
                <p class="text-2xl font-bold {{ $budget->remaining >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($budget->remaining, 2, ',', ' ') }} ₴
                </p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Використано</span>
                <span class="text-sm font-semibold {{ $budget->percentage > 100 ? 'text-red-600 dark:text-red-400' : ($budget->percentage >= $budget->alert_threshold ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                    {{ number_format($budget->percentage, 1) }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div class="h-3 rounded-full transition-all {{ $budget->percentage > 100 ? 'bg-red-600' : ($budget->percentage >= $budget->alert_threshold ? 'bg-yellow-500' : 'bg-green-600') }}" 
                     style="width: {{ min($budget->percentage, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Форма -->
    <div class="card">
        <form method="POST" action="{{ route('budgets.update', $budget) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Категорія -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Категорія витрат
                    <span class="text-gray-500 text-xs">(необов'язково, залиште порожнім для загального бюджету)</span>
                </label>
                <select 
                    id="category_id" 
                    name="category_id" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('category_id') border-red-500 @enderror"
                >
                    <option value="">-- Загальний бюджет (всі категорії) --</option>
                    @foreach(\App\Models\Category::where('type', 'expense')->where(function($q) {
                        $q->whereNull('user_id')->orWhere('user_id', auth()->id());
                    })->orderBy('name')->get() as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $budget->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Сума бюджету -->
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Сума бюджету <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        step="0.01"
                        min="0.01"
                        value="{{ old('amount', $budget->amount) }}"
                        required
                        placeholder="10000.00"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 pr-12 @error('amount') border-red-500 @enderror"
                    >
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 dark:text-gray-400">₴</span>
                    </div>
                </div>
                @error('amount')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @if($budget->spent > 0)
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                        ⚠️ Увага: вже витрачено {{ number_format($budget->spent, 2, ',', ' ') }} ₴
                    </p>
                @endif
            </div>

            <!-- Період -->
            <div>
                <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Період бюджету <span class="text-red-500">*</span>
                </label>
                <select 
                    id="period" 
                    name="period" 
                    required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('period') border-red-500 @enderror"
                >
                    <option value="daily" {{ old('period', $budget->period) === 'daily' ? 'selected' : '' }}>Щоденний</option>
                    <option value="weekly" {{ old('period', $budget->period) === 'weekly' ? 'selected' : '' }}>Тижневий</option>
                    <option value="monthly" {{ old('period', $budget->period) === 'monthly' ? 'selected' : '' }}>Місячний</option>
                    <option value="yearly" {{ old('period', $budget->period) === 'yearly' ? 'selected' : '' }}>Річний</option>
                </select>
                @error('period')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Дати -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Дата початку <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        value="{{ old('start_date', $budget->start_date->format('Y-m-d')) }}"
                        required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('start_date') border-red-500 @enderror"
                    >
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Дата закінчення <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        value="{{ old('end_date', $budget->end_date->format('Y-m-d')) }}"
                        required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('end_date') border-red-500 @enderror"
                    >
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Поріг попередження -->
            <div>
                <label for="alert_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Поріг попередження (%)
                    <span class="text-gray-500 text-xs">(необов'язково)</span>
                </label>
                <input 
                    type="number" 
                    id="alert_threshold" 
                    name="alert_threshold" 
                    min="0"
                    max="100"
                    value="{{ old('alert_threshold', $budget->alert_threshold) }}"
                    placeholder="80"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 @error('alert_threshold') border-red-500 @enderror"
                >
                @error('alert_threshold')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    ⚠️ Ви отримаєте попередження, коли витрати досягнуть цього відсотка від бюджету
                </p>
            </div>

            <!-- Попередження про зміну -->
            @if($budget->spent > 0)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300">
                            Увага при редагуванні
                        </h3>
                        <div class="mt-2 text-sm text-amber-700 dark:text-amber-400">
                            <p>У цього бюджету вже є витрати ({{ number_format($budget->spent, 2, ',', ' ') }} ₴). Зміна категорії, дат або періоду може вплинути на розрахунки.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Кнопки -->
            <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <form method="POST" action="{{ route('budgets.destroy', $budget) }}" 
                      onsubmit="return confirm('Ви впевнені, що хочете видалити цей бюджет? Ця дія незворотна.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary text-red-600 hover:text-red-700 dark:text-red-400">
                        🗑️ Видалити бюджет
                    </button>
                </form>

                <div class="flex space-x-3">
                    <a href="{{ route('budgets.index') }}" class="btn-secondary">
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

<script>
// Автоматичне оновлення дати закінчення при зміні періоду
document.getElementById('period').addEventListener('change', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    if (!startDate.value) return;
    
    const start = new Date(startDate.value);
    let end = new Date(start);
    
    switch(this.value) {
        case 'daily':
            end.setDate(end.getDate() + 1);
            break;
        case 'weekly':
            end.setDate(end.getDate() + 7);
            break;
        case 'monthly':
            end.setMonth(end.getMonth() + 1);
            break;
        case 'yearly':
            end.setFullYear(end.getFullYear() + 1);
            break;
    }
    
    endDate.value = end.toISOString().split('T')[0];
});
</script>
@endsection
