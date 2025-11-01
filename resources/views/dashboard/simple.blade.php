@extends('layouts.app')

@section('title', 'Дашборд (Simple)')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Дашборд</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Спрощена версія без API</p>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <!-- Income Card -->
    <div class="card border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Доходи</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">₴{{ number_format($stats['total_income'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Expenses Card -->
    <div class="card border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Витрати</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">₴{{ number_format($stats['total_expense'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                <svg class="w-8 h-8 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Balance Card -->
    <div class="card border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Баланс</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">₴{{ number_format($stats['balance'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="card">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Інформація</h2>
    <div class="space-y-2 text-gray-700 dark:text-gray-300">
        <p>✅ Це спрощена версія dashboard без API запитів</p>
        <p>✅ Дані завантажуються з бекенду через Blade (SSR)</p>
        <p>✅ Користувач: <strong>{{ auth()->user()->name }}</strong></p>
        <p>✅ Email: <strong>{{ auth()->user()->email }}</strong></p>
        <p>✅ Транзакцій в базі: <strong>{{ $transactions_count }}</strong></p>
        <p>✅ Категорій: <strong>{{ $categories_count }}</strong></p>
    </div>
</div>

<div class="card mt-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Тестування</h2>
    <p class="text-gray-600 dark:text-gray-400 mb-4">
        Якщо ця сторінка завантажилася швидко - проблема в API запитах на оригінальному dashboard.
    </p>
    <div class="flex gap-4">
        <a href="{{ route('dashboard') }}" class="btn-secondary">
            Повернутися до повного Dashboard
        </a>
        <a href="{{ route('test') }}" class="btn-primary">
            Тестова сторінка
        </a>
    </div>
</div>

@endsection
