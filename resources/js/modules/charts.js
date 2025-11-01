/**
 * Lazy loader для Chart.js
 * Завантажується тільки на сторінках з графіками
 */

let chartInstance = null;

/**
 * Ініціалізація Chart.js тільки коли потрібно
 */
export async function initCharts() {
    if (chartInstance) {
        return chartInstance;
    }

    // Динамічний імпорт Chart.js
    const { Chart, registerables } = await import('chart.js');
    Chart.register(...registerables);
    
    chartInstance = Chart;
    window.Chart = Chart;
    
    console.log('✓ Chart.js завантажено');
    return Chart;
}

/**
 * Перевірка чи потрібен Chart.js на поточній сторінці
 */
export function shouldLoadCharts() {
    return document.querySelector('[data-chart]') !== null ||
           document.querySelector('canvas[id*="chart"]') !== null ||
           document.querySelector('.chart-container') !== null;
}

/**
 * Автоматичне завантаження якщо є елементи для графіків
 */
export async function autoLoadCharts() {
    if (shouldLoadCharts()) {
        await initCharts();
        console.log('✓ Chart.js автоматично завантажено');
    }
}
