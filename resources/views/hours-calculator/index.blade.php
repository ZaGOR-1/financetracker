@extends('layouts.app')

@section('title', 'Калькулятор годин')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">💼 Калькулятор годин</h1>
    <p class="text-gray-700 dark:text-gray-400 mt-1">Розрахуйте свою заробітну плату на основі годинної ставки</p>
</div>

<!-- Success Message -->
@if(session('success'))
<div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
    <p class="text-green-800 dark:text-green-200">✅ {{ session('success') }}</p>
</div>
@endif

<!-- Calculator Card -->
<div class="card mb-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">🧮 Новий розрахунок</h2>
    
    <form action="{{ route('hours-calculator.store') }}" method="POST" id="calculatorForm">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Hours Input -->
            <div>
                <label for="hours" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    ⏱️ Кількість годин на день
                </label>
                <input 
                    type="number" 
                    name="hours" 
                    id="hours" 
                    step="0.01" 
                    min="0.01" 
                    max="24"
                    value="{{ old('hours', 8) }}"
                    class="input @error('hours') border-red-500 @enderror"
                    placeholder="8.00"
                    required
                    oninput="calculateSalary()">
                @error('hours')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Від 0.01 до 24 годин</p>
            </div>

            <!-- Hourly Rate Input -->
            <div>
                <label for="hourly_rate" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    💵 Ставка за годину
                </label>
                <input 
                    type="number" 
                    name="hourly_rate" 
                    id="hourly_rate" 
                    step="0.01" 
                    min="0.01"
                    value="{{ old('hourly_rate') }}"
                    class="input @error('hourly_rate') border-red-500 @enderror"
                    placeholder="100.00"
                    required
                    oninput="calculateSalary()">
                @error('hourly_rate')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Мінімум 0.01</p>
            </div>

            <!-- Currency Select -->
            <div>
                <label for="currency" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    💱 Валюта
                </label>
                <select 
                    name="currency" 
                    id="currency" 
                    class="input @error('currency') border-red-500 @enderror"
                    required
                    onchange="calculateSalary()">
                    <option value="UAH" {{ old('currency') === 'UAH' ? 'selected' : '' }}>🇺🇦 UAH (₴)</option>
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>🇺🇸 USD ($)</option>
                    <option value="PLN" {{ old('currency') === 'PLN' ? 'selected' : '' }}>🇵🇱 PLN (zł)</option>
                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>🇪🇺 EUR (€)</option>
                </select>
                @error('currency')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Name (Optional) -->
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    🏷️ Назва (необов'язково)
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name"
                    value="{{ old('name') }}"
                    class="input @error('name') border-red-500 @enderror"
                    placeholder="Наприклад: Основна робота">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Для зручності</p>
            </div>
        </div>

        <!-- Results Preview -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-xl p-6 mb-6" id="resultsPreview">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📊 Прогноз заробітку:</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Daily -->
                <div class="bg-gray-800 rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">За день</p>
                    <p class="text-2xl font-bold text-white" id="dailySalary">-</p>
                </div>

                <!-- Weekly -->
                <div class="bg-gray-800 rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">За тиждень</p>
                    <p class="text-sm text-gray-400 mb-1">(5 робочих днів)</p>
                    <p class="text-2xl font-bold text-green-400" id="weeklySalary">-</p>
                </div>

                <!-- Monthly -->
                <div class="bg-gray-800 rounded-lg p-4 shadow-sm border-2 border-blue-500">
                    <p class="text-xs font-semibold text-blue-400 uppercase mb-1">За місяць</p>
                    <p class="text-sm text-gray-400 mb-1">(≈21.67 днів)</p>
                    <p class="text-2xl font-bold text-blue-400" id="monthlySalary">-</p>
                </div>

                <!-- Yearly -->
                <div class="bg-gray-800 rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">За рік</p>
                    <p class="text-sm text-gray-400 mb-1">(260 днів)</p>
                    <p class="text-2xl font-bold text-purple-400" id="yearlySalary">-</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">
                💾 Зберегти розрахунок
            </button>
            <button type="button" onclick="resetForm()" class="btn btn-secondary">
                🔄 Скинути
            </button>
        </div>
    </form>
</div>

<!-- Saved Calculations -->
@if($calculations->count() > 0)
<div class="card">
    <h2 class="text-xl font-bold text-white mb-6">📋 Збережені розрахунки</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Дата</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Назва</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Години/день</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Ставка</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">За день</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">За місяць</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-gray-900 divide-y divide-gray-700">
                @foreach($calculations as $calc)
                <tr class="hover:bg-gray-800 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                        {{ $calc->created_at->format('d.m.Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                        {{ $calc->name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                        {{ $calc->hours }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $calc->currency_symbol }}{{ number_format($calc->hourly_rate, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $calc->formatted_daily_salary }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 dark:text-blue-400">
                        {{ $calc->formatted_monthly_salary }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <form action="{{ route('hours-calculator.destroy', $calc) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium">
                                🗑️ Видалити
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card text-center py-12">
    <div class="text-6xl mb-4">💼</div>
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Немає збережених розрахунків</h3>
    <p class="text-gray-600 dark:text-gray-400">Заповніть форму вище та збережіть свій перший розрахунок</p>
</div>
@endif

@endsection

@push('scripts')
<script>
// Символи валют
const currencySymbols = {
    'UAH': '₴',
    'USD': '$',
    'PLN': 'zł',
    'EUR': '€'
};

// Функція розрахунку зарплати
function calculateSalary() {
    const hours = parseFloat(document.getElementById('hours').value) || 0;
    const rate = parseFloat(document.getElementById('hourly_rate').value) || 0;
    const currency = document.getElementById('currency').value;
    const symbol = currencySymbols[currency];

    if (hours > 0 && rate > 0) {
        const daily = hours * rate;
        const weekly = daily * 5;
        const monthly = daily * 21.67;
        const yearly = daily * 260;

        document.getElementById('dailySalary').textContent = symbol + daily.toLocaleString('uk-UA', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('weeklySalary').textContent = symbol + weekly.toLocaleString('uk-UA', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('monthlySalary').textContent = symbol + monthly.toLocaleString('uk-UA', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('yearlySalary').textContent = symbol + yearly.toLocaleString('uk-UA', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
        document.getElementById('dailySalary').textContent = '-';
        document.getElementById('weeklySalary').textContent = '-';
        document.getElementById('monthlySalary').textContent = '-';
        document.getElementById('yearlySalary').textContent = '-';
    }
}

// Функція скидання форми
function resetForm() {
    document.getElementById('calculatorForm').reset();
    document.getElementById('hours').value = '8';
    calculateSalary();
}

// Розрахувати при завантаженні сторінки
document.addEventListener('DOMContentLoaded', function() {
    calculateSalary();
});
</script>
@endpush
