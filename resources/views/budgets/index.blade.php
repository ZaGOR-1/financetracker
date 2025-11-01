@extends('layouts.app')

@section('page', 'budgets')

@section('title', 'Бюджети')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Бюджети</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Планування та контроль витрат</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('export.budgets') }}" 
           class="btn-secondary flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Експорт
        </a>
        <a href="{{ route('budgets.create') }}" class="btn-primary">
            + Створити бюджет
        </a>
    </div>
</div>

<!-- Фільтри -->
<div class="card mb-6">
    <form method="GET" action="{{ route('budgets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Період</label>
            <select name="period" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Всі періоди</option>
                <option value="daily" {{ request('period') === 'daily' ? 'selected' : '' }}>Щоденний</option>
                <option value="weekly" {{ request('period') === 'weekly' ? 'selected' : '' }}>Тижневий</option>
                <option value="monthly" {{ request('period') === 'monthly' ? 'selected' : '' }}>Місячний</option>
                <option value="yearly" {{ request('period') === 'yearly' ? 'selected' : '' }}>Річний</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Статус</label>
            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Всі статуси</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Активні</option>
                <option value="exceeded" {{ request('status') === 'exceeded' ? 'selected' : '' }}>Перевищено</option>
                <option value="warning" {{ request('status') === 'warning' ? 'selected' : '' }}>Попередження</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full">
                Застосувати
            </button>
        </div>

        <div class="flex items-end">
            <a href="{{ route('budgets.index') }}" class="btn-secondary w-full text-center">
                Скинути
            </a>
        </div>
    </form>
</div>

<!-- Список бюджетів -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse($budgets as $budget)
    <div class="card hover:shadow-lg transition-shadow">
        <!-- Заголовок -->
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @if($budget->category)
                        <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ $budget->category->color }}"></span>
                        {{ $budget->category->name }}
                    @else
                        <span class="inline-block w-3 h-3 rounded-full mr-2 bg-gray-500"></span>
                        Загальний бюджет
                    @endif
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @switch($budget->period)
                        @case('daily') Щоденний @break
                        @case('weekly') Тижневий @break
                        @case('monthly') Місячний @break
                        @case('yearly') Річний @break
                    @endswitch
                    • {{ $budget->start_date->format('d.m.Y') }} - {{ $budget->end_date->format('d.m.Y') }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('budgets.edit', $budget) }}" 
                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
                <form method="POST" action="{{ route('budgets.destroy', $budget) }}" 
                      onsubmit="return confirm('Ви впевнені?')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Суми -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400">Бюджет</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($budget->amount, 2, ',', ' ') }} ₴</p>
            </div>
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400">Витрачено</p>
                <p class="text-lg font-semibold text-orange-600 dark:text-orange-400">{{ number_format($budget->spent, 2, ',', ' ') }} ₴</p>
            </div>
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400">Залишок</p>
                <p class="text-lg font-semibold {{ $budget->remaining >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($budget->remaining, 2, ',', ' ') }} ₴
                </p>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="mb-3">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-gray-600 dark:text-gray-400">Використано</span>
                <span class="text-sm font-semibold {{ $budget->percentage > 100 ? 'text-red-600 dark:text-red-400' : ($budget->percentage >= $budget->alert_threshold ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                    {{ number_format($budget->percentage, 1) }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all {{ $budget->percentage > 100 ? 'bg-red-600' : ($budget->percentage >= $budget->alert_threshold ? 'bg-yellow-500' : 'bg-green-600') }}" 
                     style="width: {{ min($budget->percentage, 100) }}%"></div>
            </div>
        </div>

        <!-- Статус -->
        <div class="flex items-center justify-between">
            @if($budget->percentage > 100)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                    🚨 Перевищено
                </span>
            @elseif($budget->percentage >= $budget->alert_threshold)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                    ⚠️ Попередження ({{ $budget->alert_threshold }}%)
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    ✅ Нормально
                </span>
            @endif

            @if($budget->is_active)
                <span class="text-xs text-gray-500 dark:text-gray-400">Активний</span>
            @else
                <span class="text-xs text-gray-500 dark:text-gray-400">Неактивний</span>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-2 text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Немає бюджетів</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Створіть свій перший бюджет для контролю витрат</p>
        <div class="mt-6">
            <a href="{{ route('budgets.create') }}" class="btn-primary inline-flex items-center">
                + Створити бюджет
            </a>
        </div>
    </div>
    @endforelse
</div>

<!-- Пагінація -->
@if($budgets->hasPages())
<div class="mt-6">
    {{ $budgets->links() }}
</div>
@endif
@endsection
