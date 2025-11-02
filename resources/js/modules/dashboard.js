/**
 * Dashboard модуль
 * Завантажується тільки на сторінці дашборду
 */

import { initCharts } from './charts.js';

/**
 * Ініціалізація дашборду
 */
export async function initDashboard() {
    // Завантажуємо Chart.js тільки якщо є графіки
    if (document.querySelector('[data-chart]')) {
        await initCharts();
        await loadDashboardCharts();
    }
    
    // Ініціалізація інших компонентів дашборду
    initQuickStats();
    initRecentTransactions();
}

/**
 * Завантаження графіків дашборду
 */
async function loadDashboardCharts() {
    const Chart = window.Chart;
    
    // Cashflow chart
    const cashflowCanvas = document.getElementById('cashflowChart');
    if (cashflowCanvas) {
        // Ініціалізація графіку
    }
    
    // Category breakdown chart
    const categoryCanvas = document.getElementById('categoryChart');
    if (categoryCanvas) {
        // Ініціалізація графіку
    }
}

/**
 * Швидка статистика
 */
function initQuickStats() {
    // Анімація чисел, якщо потрібно
}

/**
 * Останні транзакції
 */
function initRecentTransactions() {
    // Ініціалізація останніх транзакцій
}
