import './bootstrap';
import Alpine from 'alpinejs';
import 'flowbite';
import { logger } from './utils/logger.js';

// ============================================
// БАЗОВА ІНІЦІАЛІЗАЦІЯ (завжди завантажується)
// ============================================

// Ініціалізуємо Logger (для відслідковування помилок)
window.logger = logger;

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
        console.log('ℹ️ Базові модулі завантажено');
        return;
    }
    
    console.log(`🚀 Завантаження модулів для: ${page}`);
    
    try {
        switch (page) {
            case 'dashboard':
                const { initDashboard } = await import('./modules/dashboard.js');
                await initDashboard();
                logger.event('Dashboard loaded');
                break;
                
            case 'transactions':
                const { initTransactions } = await import('./modules/transactions.js');
                initTransactions();
                logger.event('Transactions loaded');
                break;
                
            case 'budgets':
                const { initBudgets } = await import('./modules/budgets.js');
                await initBudgets();
                logger.event('Budgets loaded');
                break;
                
            default:
                // Автоматичне завантаження Chart.js якщо є графіки
                const { autoLoadCharts } = await import('./modules/charts.js');
                await autoLoadCharts();
        }
    } catch (error) {
        logger.error('Module loading failed', {
            page,
            error: error.message,
            stack: error.stack,
        });
    }
}

// Завантажуємо модулі після DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadPageModules);
} else {
    loadPageModules();
}
