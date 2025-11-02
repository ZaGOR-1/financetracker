<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Примусове оновлення курсів через API\n";
echo str_repeat('=', 70)."\n\n";

$today = now()->format('Y-m-d');

// 1. Видаляємо сьогоднішні курси з БД
echo "🗑️  Видаляю старі курси за {$today}...\n";
$deleted = DB::table('exchange_rates')
    ->where('date', $today)
    ->delete();
echo "   Видалено: {$deleted} записів\n\n";

// 2. Очищаємо кеш
echo "🧹 Очищаю кеш...\n";
\Artisan::call('cache:clear');
echo "   ✅ Кеш очищено\n\n";

// 3. Оновлюємо через API
echo "📡 Оновлюю курси через ExchangeRate-API...\n";
echo str_repeat('-', 70)."\n";

$service = app(\App\Services\CurrencyService::class);
$results = $service->updateAllRates();

$success = 0;
$failed = 0;

foreach ($results as $pair => $result) {
    if ($result['success']) {
        echo "✅ {$pair}: ".number_format($result['rate'], 6)."\n";
        $success++;
    } else {
        echo "❌ {$pair}: ".$result['error']."\n";
        $failed++;
    }
}

echo "\n".str_repeat('=', 70)."\n";
echo "📊 Підсумок:\n";
echo "   ✅ Успішно: {$success}\n";
echo "   ❌ Помилок: {$failed}\n";

// 4. Перевіряємо що збереглося в БД
echo "\n💾 Перевірка БД:\n";
$newRates = DB::table('exchange_rates')
    ->where('date', $today)
    ->get();

foreach ($newRates as $rate) {
    echo "   {$rate->base_currency}→{$rate->target_currency}: {$rate->rate}\n";
}

echo "\n✅ Готово! Курси оновлено з API\n";
