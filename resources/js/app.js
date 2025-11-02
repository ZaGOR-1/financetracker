import './bootstrap';
import Alpine from 'alpinejs';
import 'flowbite';

// ============================================
// БАЗОВА ІНІЦІАЛІЗАЦІЯ (завжди завантажується)
// ============================================

// Ініціалізуємо Alpine.js (легкий фреймворк, завжди потрібен)
window.Alpine = Alpine;
Alpine.start();

// Темна тема (завжди активна)
document.documentElement.classList.add('dark');
localStorage.setItem('color-theme', 'dark');

// ============================================
// LAZY LOADING МОДУЛІВ
// ============================================

/**
 * Визначення поточної сторінки за data-атрибутом
 */
function getCurrentPage() {
    const body = document.body;
    return body.dataset.page || null;
}

/**
 * Lazy loading для конкретних сторінок
 */
async function loadPageModules() {
    const page = getCurrentPage();
    
    if (!page) {
        if (import.meta.env.DEV) {
            console.log('ℹ️ Базові модулі завантажено');
        }
        return;
    }
    
    if (import.meta.env.DEV) {
        console.log(`🚀 Завантаження модулів для: ${page}`);
    }
    
    try {
        switch (page) {
            case 'dashboard':
                const { initDashboard } = await import('./modules/dashboard.js');
                await initDashboard();
                break;
                
            case 'transactions':
                const { initTransactions } = await import('./modules/transactions.js');
                initTransactions();
                break;
                
            case 'budgets':
                const { initBudgets } = await import('./modules/budgets.js');
                await initBudgets();
                break;
                
            default:
                // Автоматичне завантаження Chart.js якщо є графіки
                const { autoLoadCharts } = await import('./modules/charts.js');
                await autoLoadCharts();
        }
    } catch (error) {
        if (import.meta.env.DEV) {
            console.error('❌ Помилка завантаження модуля:', error);
        }
    }
}

// Завантажуємо модулі після DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadPageModules);
} else {
    loadPageModules();
}
