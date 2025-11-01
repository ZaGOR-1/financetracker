@extends('layouts.app')

@section('page', 'budgets')

@section('title', 'Створити бюджет')

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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Створити бюджет</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Встановіть ліміт витрат для контролю фінансів</p>
            </div>
        </div>
    </div>

    <!-- Форма -->
    <div class="card">
        <form method="POST" action="{{ route('budgets.store') }}" class="space-y-6">
            @csrf

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
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    💡 Виберіть категорію для контролю конкретних витрат або залиште порожнім для загального бюджету
                </p>
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
                        value="{{ old('amount') }}"
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
                    <option value="">-- Оберіть період --</option>
                    <option value="daily" {{ old('period') === 'daily' ? 'selected' : '' }}>Щоденний</option>
                    <option value="weekly" {{ old('period') === 'weekly' ? 'selected' : '' }}>Тижневий</option>
                    <option value="monthly" {{ old('period') === 'monthly' ? 'selected' : '' }}>Місячний</option>
                    <option value="yearly" {{ old('period') === 'yearly' ? 'selected' : '' }}>Річний</option>
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
                        value="{{ old('start_date', date('Y-m-d')) }}"
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
                        value="{{ old('end_date', date('Y-m-d', strtotime('+1 month'))) }}"
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
                    value="{{ old('alert_threshold', 80) }}"
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
                            Як працюють бюджети?
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Система автоматично відслідковує ваші витрати відносно бюджету</li>
                                <li>Ви отримаєте сповіщення при досягненні порогу попередження</li>
                                <li>Загальний бюджет враховує всі категорії витрат</li>
                                <li>Бюджет категорії враховує тільки витрати в цій категорії</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('budgets.index') }}" class="btn-secondary">
                    Скасувати
                </a>
                <button type="submit" class="btn-primary">
                    💾 Створити бюджет
                </button>
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

// Автоматичне оновлення дати закінчення при зміні дати початку
document.getElementById('start_date').addEventListener('change', function() {
    const period = document.getElementById('period').value;
    if (period) {
        document.getElementById('period').dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
