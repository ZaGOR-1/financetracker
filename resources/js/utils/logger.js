/**
 * Logger utility для фронтенду
 * Відправляє критичні помилки та події на бекенд
 */

class Logger {
    constructor() {
        this.endpoint = '/api/log';
        this.isDevelopment = import.meta.env.DEV;
        this.initGlobalErrorHandler();
    }

    /**
     * Ініціалізація глобального обробника помилок
     */
    initGlobalErrorHandler() {
        // Перехоплюємо необроблені помилки JavaScript
        window.addEventListener('error', (event) => {
            this.error('Uncaught Error', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack,
            });
        });

        // Перехоплюємо необроблені promise rejection
        window.addEventListener('unhandledrejection', (event) => {
            this.error('Unhandled Promise Rejection', {
                reason: event.reason,
                promise: event.promise,
            });
        });
    }

    /**
     * Логування помилки
     */
    error(message, context = {}) {
        this.log('error', message, context);
    }

    /**
     * Логування попередження
     */
    warn(message, context = {}) {
        this.log('warning', message, context);
    }

    /**
     * Логування інформації
     */
    info(message, context = {}) {
        this.log('info', message, context);
    }

    /**
     * Логування debug інформації (тільки у development)
     */
    debug(message, context = {}) {
        if (this.isDevelopment) {
            this.log('debug', message, context);
        }
    }

    /**
     * Базовий метод логування
     */
    log(level, message, context = {}) {
        const logData = {
            level,
            message,
            context,
            url: window.location.href,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
        };

        // Виводимо в консоль у development
        if (this.isDevelopment) {
            const consoleMethod = level === 'error' ? 'error' : level === 'warning' ? 'warn' : 'log';
            console[consoleMethod](`[${level.toUpperCase()}]`, message, context);
        }

        // Відправляємо на сервер тільки error та warning
        if (level === 'error' || level === 'warning') {
            this.sendToBackend(logData);
        }
    }

    /**
     * Відправка логу на бекенд
     */
    async sendToBackend(logData) {
        try {
            await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(logData),
            });
        } catch (error) {
            // Якщо не вдалося відправити лог - виводимо в консоль
            if (this.isDevelopment) {
                console.error('Failed to send log to backend:', error);
            }
        }
    }

    /**
     * Логування користувацької події
     */
    event(eventName, data = {}) {
        this.info(`Event: ${eventName}`, data);
    }

    /**
     * Логування API запиту
     */
    apiRequest(method, url, data = {}) {
        this.debug(`API Request: ${method} ${url}`, data);
    }

    /**
     * Логування API помилки
     */
    apiError(method, url, error, response = null) {
        this.error(`API Error: ${method} ${url}`, {
            error: error.message || error,
            response: response?.data || null,
            status: response?.status || null,
        });
    }
}

// Експортуємо singleton instance
export const logger = new Logger();

// Додаємо до window для глобального доступу
window.logger = logger;
