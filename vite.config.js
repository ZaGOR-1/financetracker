import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Мініфікація за допомогою terser
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Видалити console.log у production
                drop_debugger: true, // Видалити debugger statements
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
            },
            format: {
                comments: false, // Видалити всі коментарі
            },
        },
        // Оптимізований Code splitting
        rollupOptions: {
            output: {
                // Автоматичний code splitting для динамічних імпортів
                manualChunks(id) {
                    // Chart.js у окремий chunk (великий розмір ~200KB)
                    if (id.includes('chart.js')) {
                        return 'chart';
                    }
                    // Alpine.js у окремий chunk (~15KB)
                    if (id.includes('alpinejs')) {
                        return 'alpine';
                    }
                    // Flowbite у окремий chunk (~40KB)
                    if (id.includes('flowbite')) {
                        return 'flowbite';
                    }
                    // Модулі застосунку
                    if (id.includes('/modules/dashboard')) {
                        return 'dashboard';
                    }
                    if (id.includes('/modules/transactions')) {
                        return 'transactions';
                    }
                    if (id.includes('/modules/budgets')) {
                        return 'budgets';
                    }
                    // Vendor dependencies у окремий chunk
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
                // Оптимізація імен файлів для кешування
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(ext)) {
                        return `images/[name]-[hash][extname]`;
                    }
                    if (/woff|woff2|eot|ttf|otf/i.test(ext)) {
                        return `fonts/[name]-[hash][extname]`;
                    }
                    return `[ext]/[name]-[hash][extname]`;
                },
            },
        },
        // Збільшити ліміт для попередження
        chunkSizeWarningLimit: 600,
        // Оптимізація CSS
        cssCodeSplit: true,
        // Зменшення розміру bundle
        reportCompressedSize: true,
        // Source maps для production (опціонально, вимкніть для менших файлів)
        sourcemap: false,
    },
    // Оптимізація для dev режиму
    server: {
        hmr: {
            overlay: true,
        },
        // Швидше перезавантаження
        watch: {
            usePolling: false,
        },
    },
    // Оптимізація залежностей
    optimizeDeps: {
        include: ['alpinejs', 'flowbite'],
        // Chart.js буде завантажуватись динамічно, не включаємо
        exclude: ['chart.js'],
    },
});
