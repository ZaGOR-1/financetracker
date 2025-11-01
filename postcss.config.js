export default {
  plugins: {
    // Tailwind CSS
    tailwindcss: {},
    
    // Autoprefixer - додає vendor prefixes
    autoprefixer: {},
    
    // cssnano - мінімізація CSS для production
    ...(process.env.NODE_ENV === 'production' ? {
      cssnano: {
        preset: ['default', {
          // Видалити коментарі
          discardComments: {
            removeAll: true,
          },
          // Мінімізувати calc()
          calc: true,
          // Мінімізувати colors
          colormin: true,
          // Об'єднати правила
          mergeRules: true,
          // Видалити дублікати
          discardDuplicates: true,
          // Мінімізувати шрифти
          minifyFontValues: true,
          // Мінімізувати селектори
          minifySelectors: true,
          // Нормалізувати whitespace
          normalizeWhitespace: true,
        }],
      },
    } : {}),
  },
}
