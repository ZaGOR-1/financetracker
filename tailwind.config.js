/** @type {import('tailwindcss').Config} */
export default {
  content: [
    // Laravel Blade templates
    "./resources/**/*.blade.php",
    
    // JavaScript files (Alpine.js, Chart.js, etc.)
    "./resources/**/*.js",
    
    // Vue components (якщо використовуються)
    "./resources/**/*.vue",
    
    // Flowbite components
    "./node_modules/flowbite/**/*.js",
    
    // PHP files з HTML класами (Controllers, Components)
    "./app/View/Components/**/*.php",
    
    // Public test files
    "./public/**/*.html",
  ],
  
  darkMode: 'class',
  
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
      },
    },
  },
  
  // Safelist - тільки критичні динамічні класи
  safelist: [
    // Alpine.js
    'x-cloak',
    
    // Loading states
    'loading',
    'animate-spin',
    'animate-pulse',
    
    // Custom classes (якщо генеруються динамічно)
    'period-btn',
    'currency-btn',
    'chart-container',
  ],
  
  plugins: [
    require('flowbite/plugin')
  ],
}
