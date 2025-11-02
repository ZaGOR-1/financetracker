@extends('layouts.app')

@section('page', 'dashboard')

@section('title', 'Дашборд')

@push('styles')
<style>
    .chart-container {
        position: relative !important;
        height: 280px !important;
        max-height: 280px !important;
        min-height: 280px !important;
        width: 100% !important;
        padding-bottom: 10px !important;
    }
    
    .chart-container canvas {
        max-width: 100% !important;
        max-height: 280px !important;
    }
    
    /* Запобігти розтягуванню card */
    .card:has(.chart-container) {
        overflow: hidden !important;
    }
    
    /* Responsive chart container */
    @media (max-width: 640px) {
        .chart-container {
            height: 240px !important;
            max-height: 240px !important;
            min-height: 240px !important;
        }
        
        .chart-container canvas {
            max-height: 240px !important;
        }
    }
    
    /* Period buttons */
    .period-btn {
        transition: all 0.2s ease;
    }
    
    .period-btn:not(.bg-blue-600) {
        color: #374151;
    }
    
    .dark .period-btn:not(.bg-blue-600) {
        color: #d1d5db;
    }
    
    .period-btn:not(.bg-blue-600):hover {
        background-color: #e5e7eb;
    }
    
    .dark .period-btn:not(.bg-blue-600):hover {
        background-color: #4b5563;
    }
    
    /* Currency buttons */
    .currency-btn, .dashboard-currency-btn {
        transition: all 0.2s ease;
    }
    
    .currency-btn:not(.bg-blue-600), .dashboard-currency-btn:not(.bg-blue-600) {
        color: #374151;
    }
    
    .dark .currency-btn:not(.bg-blue-600), .dark .dashboard-currency-btn:not(.bg-blue-600) {
        color: #d1d5db;
    }
    
    .currency-btn:not(.bg-blue-600):hover, .dashboard-currency-btn:not(.bg-blue-600):hover {
        background-color: #e5e7eb;
    }
    
    .dark .currency-btn:not(.bg-blue-600):hover, .dark .dashboard-currency-btn:not(.bg-blue-600):hover {
        background-color: #4b5563;
    }
</style>
@endpush

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Дашборд</h1>
        <p class="text-gray-700 dark:text-gray-400 mt-1">Огляд ваших фінансів</p>
    </div>
    
    <!-- Currency Selector -->
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600 dark:text-gray-400">Валюта:</span>
        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-1">
            <button onclick="changeDashboardCurrency('UAH')" 
                    class="dashboard-currency-btn px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200 bg-blue-600 text-white"
                    data-currency="UAH">
                UAH (₴)
            </button>
            <button onclick="changeDashboardCurrency('USD')" 
                    class="dashboard-currency-btn px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200"
                    data-currency="USD">
                USD ($)
            </button>
            <button onclick="changeDashboardCurrency('PLN')" 
                    class="dashboard-currency-btn px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200"
                    data-currency="PLN">
                PLN (zł)
            </button>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6" id="kpi-cards">
    <!-- Income Card -->
    <div class="card border-l-4 border-green-600 bg-gradient-to-br from-gray-50 to-green-50 dark:from-gray-800 dark:to-gray-800 overflow-hidden">
        <div class="flex items-center justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-400 uppercase tracking-wide truncate">Доходи</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1 truncate" id="total-income">
                    <span class="loading">...</span>
                </p>
            </div>
            <div class="p-2 sm:p-3 bg-green-100 dark:bg-green-900/50 rounded-xl shadow-sm flex-shrink-0">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-700 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Expenses Card -->
    <div class="card border-l-4 border-red-600 bg-gradient-to-br from-gray-50 to-red-50 dark:from-gray-800 dark:to-gray-800 overflow-hidden">
        <div class="flex items-center justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-400 uppercase tracking-wide truncate">Витрати</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1 truncate" id="total-expense">
                    <span class="loading">...</span>
                </p>
            </div>
            <div class="p-2 sm:p-3 bg-red-100 dark:bg-red-900/50 rounded-xl shadow-sm flex-shrink-0">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-700 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Balance Card -->
    <div class="card border-l-4 border-blue-600 bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-800 dark:to-gray-800 overflow-hidden">
        <div class="flex items-center justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-400 uppercase tracking-wide truncate">Баланс</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1 truncate" id="balance">
                    <span class="loading">...</span>
                </p>
            </div>
            <div class="p-2 sm:p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl shadow-sm flex-shrink-0">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-700 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Cashflow Chart -->
    <div class="card bg-gray-800" style="min-height: auto; overflow: hidden;">
        <div class="mb-4 pb-3 border-b border-gray-700">
            <!-- Заголовок -->
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                <h2 class="text-xl font-bold text-white">📈 Cashflow</h2>
                
                <!-- Period Selector -->
                <div class="flex gap-1 bg-gray-700 rounded-lg p-1 overflow-x-auto max-w-full">
                    <button onclick="changeCashflowPeriod('7d')" 
                            class="period-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 whitespace-nowrap"
                            data-period="7d">
                        7д
                    </button>
                    <button onclick="changeCashflowPeriod('14d')" 
                            class="period-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 whitespace-nowrap"
                            data-period="14d">
                        14д
                    </button>
                    <button onclick="changeCashflowPeriod('30d')" 
                            class="period-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 whitespace-nowrap"
                            data-period="30d">
                        30д
                    </button>
                    <button onclick="changeCashflowPeriod('3m')" 
                            class="period-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 whitespace-nowrap"
                            data-period="3m">
                        3м
                    </button>
                    <button onclick="changeCashflowPeriod('6m')" 
                            class="period-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 bg-blue-600 text-white whitespace-nowrap"
                            data-period="6m">
                        6м
                    </button>
                </div>
            </div>
            
            <!-- Currency Selector -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs sm:text-sm text-gray-400 whitespace-nowrap">Валюта:</span>
                <div class="flex gap-1 bg-gray-700 rounded-lg p-1 overflow-x-auto max-w-full flex-1">
                    <button onclick="changeCashflowCurrency('UAH')" 
                            class="currency-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 bg-blue-600 text-white whitespace-nowrap"
                            data-currency="UAH">
                        ₴ UAH
                    </button>
                    <button onclick="changeCashflowCurrency('USD')" 
                            class="currency-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 whitespace-nowrap"
                            data-currency="USD">
                        $ USD
                    </button>
                    <button onclick="changeCashflowCurrency('PLN')" 
                            class="currency-btn px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-md transition-colors duration-200 whitespace-nowrap"
                            data-currency="PLN">
                        zł PLN
                    </button>
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="cashflowChart"></canvas>
        </div>
    </div>

    <!-- Category Breakdown Chart -->
    <div class="card bg-gray-800">
        <h2 class="text-xl font-bold text-white mb-4 pb-3 border-b border-gray-700">📊 Витрати за категоріями</h2>
        <div class="chart-container">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Categories -->
<div class="card bg-gray-800">
    <h2 class="text-xl font-bold text-white mb-4 pb-3 border-b border-gray-700">🏆 Топ-5 категорій витрат</h2>
    <div id="top-categories" class="space-y-3">
        <p class="text-gray-400">Завантаження...</p>
    </div>
</div>

@push('scripts')
<script>
// Глобальна змінна для поточної валюти дашборду
let currentDashboardCurrency = 'UAH';

// Глобальні змінні для headers
let dashboardHeaders = null;
let dashboardFetchWithTimeout = null;

// Функція зміни валюти дашборду
function changeDashboardCurrency(currency) {
    if (currentDashboardCurrency === currency) return;
    
    currentDashboardCurrency = currency;
    
    // Оновлюємо активну кнопку
    document.querySelectorAll('.dashboard-currency-btn').forEach(btn => {
        if (btn.dataset.currency === currency) {
            btn.classList.add('bg-blue-600', 'text-white');
            btn.classList.remove('hover:bg-gray-100', 'dark:hover:bg-gray-700');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('hover:bg-gray-100', 'dark:hover:bg-gray-700');
        }
    });
    
    // Перезавантажуємо дані дашборду з новою валютою
    loadDashboardData(currency);
    
    // Зберігаємо вибір у localStorage
    localStorage.setItem('dashboardCurrency', currency);
}

// Глобальна функція завантаження даних дашборду
function loadDashboardData(currency = null) {
    const url = currency 
        ? `/api/v1/stats/overview?currency=${currency}` 
        : '/api/v1/stats/overview';
    
    dashboardFetchWithTimeout(url, {
        headers: dashboardHeaders,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response) return null;
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (!data) {
            // Timeout або інша помилка
            document.getElementById('total-income').innerHTML = '<span class="text-red-500">Помилка</span>';
            document.getElementById('total-expense').innerHTML = '<span class="text-red-500">Помилка</span>';
            document.getElementById('balance').innerHTML = '<span class="text-red-500">Помилка</span>';
            document.getElementById('top-categories').innerHTML = '<p class="text-red-500 dark:text-red-400">Помилка завантаження даних</p>';
            return;
        }
        
        if (data.success) {
            const stats = data.data;
            // Отримуємо символ валюти
            const currencySymbols = {
                'UAH': '₴',
                'USD': '$',
                'PLN': 'zł',
                'EUR': '€'
            };
            const currencySymbol = currencySymbols[stats.currency] || stats.currency;
            
            document.getElementById('total-income').textContent = `${currencySymbol}${stats.total_income.toLocaleString('uk-UA', {minimumFractionDigits: 2})}`;
            document.getElementById('total-expense').textContent = `${currencySymbol}${stats.total_expense.toLocaleString('uk-UA', {minimumFractionDigits: 2})}`;
            document.getElementById('balance').textContent = `${currencySymbol}${stats.balance.toLocaleString('uk-UA', {minimumFractionDigits: 2})}`;
            
            // Top categories
            if (stats.top_expense_categories && stats.top_expense_categories.length > 0) {
                const topCategoriesHtml = stats.top_expense_categories.map(cat => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full" style="background-color: ${cat.category_color}"></div>
                            <span class="font-medium text-gray-900 dark:text-white">${cat.category_name}</span>
                        </div>
                        <span class="text-gray-900 dark:text-white font-semibold">${currencySymbol}${cat.total.toLocaleString('uk-UA', {minimumFractionDigits: 2})}</span>
                    </div>
                `).join('');
                document.getElementById('top-categories').innerHTML = topCategoriesHtml;
            } else {
                document.getElementById('top-categories').innerHTML = '<p class="text-gray-400">Немає даних про витрати</p>';
            }
        } else {
            console.error('API returned error:', data.message);
            document.getElementById('total-income').textContent = '₴0.00';
            document.getElementById('total-expense').textContent = '₴0.00';
            document.getElementById('balance').textContent = '₴0.00';
            document.getElementById('top-categories').innerHTML = '<p class="text-gray-400">Немає даних</p>';
        }
    })
    .catch(err => {
        console.error('Error fetching overview:', err);
        document.getElementById('total-income').innerHTML = '<span class="text-red-500 text-sm">Помилка</span>';
        document.getElementById('total-expense').innerHTML = '<span class="text-red-500 text-sm">Помилка</span>';
        document.getElementById('balance').innerHTML = '<span class="text-red-500 text-sm">Помилка</span>';
        document.getElementById('top-categories').innerHTML = '<p class="text-red-400">Помилка завантаження. Перевірте консоль браузера (F12)</p>';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Use session-based auth (no token needed for web routes)
    dashboardHeaders = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
    };
    
    // Fetch Overview Data with timeout
    dashboardFetchWithTimeout = (url, options, timeout = 5000) => {
        return Promise.race([
            fetch(url, options),
            new Promise((_, reject) => 
                setTimeout(() => reject(new Error('Timeout')), timeout)
            )
        ]);
    };
    
    // Відновлюємо вибір валюти з localStorage
    const savedCurrency = localStorage.getItem('dashboardCurrency');
    if (savedCurrency && ['UAH', 'USD', 'PLN'].includes(savedCurrency)) {
        currentDashboardCurrency = savedCurrency;
        // Оновлюємо активну кнопку
        document.querySelectorAll('.dashboard-currency-btn').forEach(btn => {
            if (btn.dataset.currency === savedCurrency) {
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('hover:bg-gray-100', 'dark:hover:bg-gray-700');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
            }
        });
    }
    
    // Завантажуємо дані при старті
    loadDashboardData(currentDashboardCurrency);

    // Global variable for cashflow chart
    let cashflowChart = null;
    let currentPeriod = '6m';
    let currentCurrency = 'UAH';

    // Function to load cashflow data
    function loadCashflowData(period = '6m', currency = null) {
        currentPeriod = period;
        if (currency) {
            currentCurrency = currency;
        }
        
        const url = `/api/v1/stats/cashflow?period=${period}${currency ? '&currency=' + currency : ''}`;
        
        fetch(url, {
            headers: dashboardHeaders,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Cashflow API response:', data);
            
            if (data.success) {
                const cashflow = data.data.cashflow;
                const currency = data.data.currency || 'UAH';
                const ctx = document.getElementById('cashflowChart');
                
                console.log('Cashflow data points:', cashflow.length);
                console.log('Currency:', currency);
                
                if (!ctx) {
                    console.error('Canvas element not found!');
                    return;
                }
                
                // Currency symbols mapping
                const currencySymbols = {
                    'UAH': '₴',
                    'USD': '$',
                    'PLN': 'zł',
                    'EUR': '€'
                };
                
                const currencySymbol = currencySymbols[currency] || currency;
                
                // Destroy old chart if exists
                if (cashflowChart) {
                    console.log('Destroying old chart...');
                    cashflowChart.destroy();
                    cashflowChart = null;
                }
                
                console.log('Creating new chart...');
                
                // Log data for debugging
                console.log('Labels:', cashflow.map(item => item.period));
                console.log('Income data:', cashflow.map(item => item.income));
                console.log('Expense data:', cashflow.map(item => item.expense));
                
                // Create new chart
                cashflowChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: cashflow.map(item => item.period),
                        datasets: [
                            {
                                label: 'Доходи',
                                data: cashflow.map(item => item.income),
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Витрати',
                                data: cashflow.map(item => item.expense),
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.4,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 750,
                            easing: 'easeInOutQuart'
                        },
                        layout: {
                            padding: {
                                top: 5,
                                bottom: 15,
                                left: 5,
                                right: 5
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: `Cashflow (${currencySymbol})`,
                                color: '#f9fafb',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                },
                                padding: {
                                    bottom: 10
                                }
                            },
                            legend: {
                                labels: {
                                    color: '#f9fafb',
                                    font: {
                                        size: 14,
                                        weight: '600'
                                    },
                                    padding: 10
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grace: '5%',
                                ticks: {
                                    color: '#9ca3af',
                                    callback: function(value) {
                                        return value.toLocaleString('uk-UA') + ' ' + currencySymbol;
                                    }
                                },
                                grid: {
                                    color: '#374151'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#9ca3af'
                                },
                                grid: {
                                    color: '#374151'
                                }
                            }
                        }
                    }
                });
                
                console.log('Chart created successfully!');
            } else {
                console.error('API returned success=false:', data.message);
                const container = document.getElementById('cashflowChart').parentElement.parentElement;
                container.innerHTML = '<p class="text-gray-400 text-center py-8">Немає даних для відображення</p>';
            }
        })
        .catch(err => {
            console.error('Error fetching cashflow:', err);
            const container = document.getElementById('cashflowChart').parentElement.parentElement;
            container.innerHTML = '<p class="text-red-400 text-center py-8">Помилка завантаження даних</p>';
        });
    }
    
    // Function to change period
    window.changeCashflowPeriod = function(period) {
        // Update button styles
        document.querySelectorAll('.period-btn').forEach(btn => {
            if (btn.dataset.period === period) {
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('text-gray-300', 'hover:bg-gray-600');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('text-gray-300', 'hover:bg-gray-600');
            }
        });
        
        // Load new data
        loadCashflowData(period, currentCurrency);
    };
    
    // Function to change currency
    window.changeCashflowCurrency = function(currency) {
        currentCurrency = currency;
        
        // Update button styles
        document.querySelectorAll('.currency-btn').forEach(btn => {
            if (btn.dataset.currency === currency) {
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('text-gray-300', 'hover:bg-gray-600');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('text-gray-300', 'hover:bg-gray-600');
            }
        });
        
        // Load new data
        loadCashflowData(currentPeriod, currency);
    };
    
    // Initial load
    loadCashflowData('6m', 'UAH');

    // Fetch Category Breakdown
    fetch('/api/v1/stats/category-breakdown', {
        headers: dashboardHeaders,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const breakdown = data.data.breakdown;
            const ctx = document.getElementById('categoryChart');
            
            if (breakdown.length === 0) {
                const container = ctx.parentElement.parentElement;
                container.innerHTML = '<p class="text-gray-400 text-center py-8">Немає даних для відображення</p>';
                return;
            }
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: breakdown.map(item => item.category_name),
                    datasets: [{
                        data: breakdown.map(item => item.total),
                        backgroundColor: breakdown.map(item => item.category_color),
                        borderWidth: 2,
                        borderColor: '#1f2937'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#f9fafb',
                                padding: 15,
                                font: {
                                    size: 14,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const percentage = breakdown[context.dataIndex].percentage;
                                    return `${label}: ₴${value.toLocaleString('uk-UA', {minimumFractionDigits: 2})} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.error('Failed to load category breakdown:', data.message);
            const container = document.getElementById('categoryChart').parentElement.parentElement;
            container.innerHTML = '<p class="text-gray-400 text-center py-8">Немає даних для відображення</p>';
        }
    })
    .catch(err => {
        console.error('Error fetching category breakdown:', err);
        const container = document.getElementById('categoryChart').parentElement.parentElement;
        container.innerHTML = '<p class="text-red-400 text-center py-8">Помилка завантаження даних</p>';
    });
});
</script>
@endpush

@endsection
