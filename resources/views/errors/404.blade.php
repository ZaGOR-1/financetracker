@extends('layouts.app')

@section('title', 'Сторінка не знайдена')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <!-- Іконка 404 -->
        <div class="mb-8">
            <svg class="mx-auto h-32 w-32 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <!-- Заголовок -->
        <h1 class="text-6xl font-bold text-gray-900 dark:text-white mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
            Сторінку не знайдено
        </h2>
        
        <!-- Опис -->
        <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
            На жаль, сторінка, яку ви шукаєте, не існує або була переміщена.
            Перевірте правильність адреси або поверніться на головну сторінку.
        </p>

        <!-- Кнопки -->
        <div class="flex justify-center space-x-4">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                🏠 На головну
            </a>
            <button onclick="history.back()" class="btn-secondary">
                ← Назад
            </button>
        </div>

        <!-- Швидкі посилання -->
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Популярні розділи:</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('transactions.index') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 text-sm">
                    Транзакції
                </a>
                <span class="text-gray-300 dark:text-gray-700">•</span>
                <a href="{{ route('budgets.index') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 text-sm">
                    Бюджети
                </a>
                <span class="text-gray-300 dark:text-gray-700">•</span>
                <a href="{{ route('categories.index') }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 text-sm">
                    Категорії
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
