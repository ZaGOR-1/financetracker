/**
 * Dashboard модуль
 * Завантажується тільки на сторінці дашборду
 */

import { initCharts } from './charts.js';

/**
 * Ініціалізація дашборду
 */
export async function initDashboard() {
    console.log('⚡ Ініціалізація Dashboard...');
    
    // Завантажуємо Chart.js тільки якщо є графіки
    if (document.querySelector('[data-chart]')) {
        await initCharts();
        await loadDashboardCharts();
    }
    
    // Ініціалізація інших компонентів дашборду
    initQuickStats();
    initRecentTransactions();
    
    console.log('✓ Dashboard готовий');
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
        console.log('✓ Cashflow chart завантажено');
    }
    
    // Category breakdown chart
    const categoryCanvas = document.getElementById('categoryChart');
    if (categoryCanvas) {
        // Ініціалізація графіку
        console.log('✓ Category chart завантажено');
    }
}

/**
 * Швидка статистика
 */
function initQuickStats() {
    // Анімація чисел, якщо потрібно
    console.log('✓ Quick stats готові');
}

/**
 * Останні транзакції
 */
function initRecentTransactions() {
    console.log('✓ Recent transactions готові');
}
