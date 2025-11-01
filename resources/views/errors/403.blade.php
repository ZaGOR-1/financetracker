@extends('layouts.app')

@section('title', 'Доступ заборонено')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <!-- Іконка 403 -->
        <div class="mb-8">
            <svg class="mx-auto h-32 w-32 text-amber-400 dark:text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>

        <!-- Заголовок -->
        <h1 class="text-6xl font-bold text-gray-900 dark:text-white mb-4">403</h1>
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
            Доступ заборонено
        </h2>
        
        <!-- Опис -->
        <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
            У вас немає прав доступу до цієї сторінки або ресурсу.
            Якщо ви вважаєте, що це помилка, зверніться до адміністратора.
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
    </div>
</div>
@endsection
