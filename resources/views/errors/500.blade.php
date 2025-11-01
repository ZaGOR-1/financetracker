@extends('layouts.app')

@section('title', 'Помилка сервера')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <!-- Іконка 500 -->
        <div class="mb-8">
            <svg class="mx-auto h-32 w-32 text-red-400 dark:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <!-- Заголовок -->
        <h1 class="text-6xl font-bold text-gray-900 dark:text-white mb-4">500</h1>
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
            Помилка сервера
        </h2>
        
        <!-- Опис -->
        <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
            Вибачте, сталася внутрішня помилка сервера. 
            Ми вже працюємо над вирішенням проблеми.
            Спробуйте повторити запит пізніше.
        </p>

        @if(config('app.debug'))
        <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg max-w-2xl mx-auto">
            <p class="text-sm text-red-800 dark:text-red-200 font-mono text-left">
                @if(isset($exception))
                    {{ $exception->getMessage() }}
                @else
                    Детальна інформація про помилку відсутня
                @endif
            </p>
        </div>
        @endif

        <!-- Кнопки -->
        <div class="flex justify-center space-x-4">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                🏠 На головну
            </a>
            <button onclick="location.reload()" class="btn-secondary">
                🔄 Оновити сторінку
            </button>
        </div>

        <!-- Контактна інформація -->
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Якщо проблема повторюється, зв'яжіться з підтримкою
            </p>
        </div>
    </div>
</div>
@endsection
