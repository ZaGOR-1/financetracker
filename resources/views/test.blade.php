@extends('layouts.app')

@section('title', 'Тест')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Тестова сторінка</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Перевірка роботи без API</p>
</div>

<!-- Simple Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card border-l-4 border-green-500">
        <h3 class="text-lg font-bold">Картка 1</h3>
        <p class="text-gray-600 dark:text-gray-400">Статичний контент</p>
    </div>
    
    <div class="card border-l-4 border-blue-500">
        <h3 class="text-lg font-bold">Картка 2</h3>
        <p class="text-gray-600 dark:text-gray-400">Без API запитів</p>
    </div>
    
    <div class="card border-l-4 border-purple-500">
        <h3 class="text-lg font-bold">Картка 3</h3>
        <p class="text-gray-600 dark:text-gray-400">Швидке завантаження</p>
    </div>
</div>

<div class="card">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Інформація</h2>
    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
        <li>✅ Laravel працює</li>
        <li>✅ Blade шаблони рендеряться</li>
        <li>✅ Tailwind CSS завантажений</li>
        <li>✅ JavaScript працює</li>
        <li id="js-test">⏳ JavaScript перевірка...</li>
    </ul>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Простий JS тест
    document.getElementById('js-test').textContent = '✅ JavaScript працює!';
    
    console.log('✅ Сторінка завантажена');
    console.log('✅ DOMContentLoaded спрацював');
});
</script>
@endpush

@endsection
