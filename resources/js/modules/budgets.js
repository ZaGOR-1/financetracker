/**
 * Budgets модуль
 * Завантажується тільки на сторінці бюджетів
 */

import { initCharts } from './charts.js';

/**
 * Ініціалізація сторінки бюджетів
 */
export async function initBudgets() {
    console.log('⚡ Ініціалізація Budgets...');
    
    initBudgetCards();
    initProgressBars();
    
    // Завантажуємо Chart.js тільки якщо є графіки
    if (document.querySelector('[data-budget-chart]')) {
        await initCharts();
        await loadBudgetCharts();
    }
    
    console.log('✓ Budgets готові');
}

/**
 * Картки бюджетів
 */
function initBudgetCards() {
    const cards = document.querySelectorAll('[data-budget-card]');
    if (cards.length === 0) return;
    
    // Логіка карток
    console.log('✓ Budget cards готові');
}

/**
 * Progress bars з анімацією
 */
function initProgressBars() {
    const progressBars = document.querySelectorAll('[data-progress]');
    if (progressBars.length === 0) return;
    
    progressBars.forEach(bar => {
        const percentage = bar.dataset.progress;
        animateProgressBar(bar, percentage);
    });
    
    console.log('✓ Progress bars готові');
}

/**
 * Анімація progress bar
 */
function animateProgressBar(element, targetPercentage) {
    let current = 0;
    const increment = targetPercentage / 50; // 50 кроків анімації
    
    const animate = () => {
        current += increment;
        if (current >= targetPercentage) {
            current = targetPercentage;
        }
        
        element.style.width = `${current}%`;
        
        if (current < targetPercentage) {
            requestAnimationFrame(animate);
        }
    };
    
    requestAnimationFrame(animate);
}

/**
 * Графіки бюджетів
 */
async function loadBudgetCharts() {
    const Chart = window.Chart;
    
    const budgetCanvas = document.getElementById('budgetChart');
    if (budgetCanvas) {
        // Ініціалізація графіку
        console.log('✓ Budget chart завантажено');
    }
}
