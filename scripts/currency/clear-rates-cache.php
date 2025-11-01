<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧹 Очищення кешу курсів валют\n\n";

// Очистимо всі ключі exchange_rate:*
$keys = [
    'exchange_rate:USD:UAH:2025-10-06',
    'exchange_rate:PLN:UAH:2025-10-06',
    'exchange_rate:USD:PLN:2025-10-06',
    'exchange_rate:UAH:USD:2025-10-06',
    'exchange_rate:UAH:PLN:2025-10-06',
    'exchange_rate:PLN:USD:2025-10-06',
    // Історичні дати
    'exchange_rate:USD:UAH:2025-10-04',
    'exchange_rate:USD:UAH:2025-10-01',
    'exchange_rate:PLN:UAH:2025-10-02',
];

foreach ($keys as $key) {
    Cache::forget($key);
    echo "✅ Очищено: {$key}\n";
}

echo "\n✅ Кеш курсів очищено!\n";
echo "💡 Тепер конвертація використає курси з БД\n";

