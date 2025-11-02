<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Оновлення курсів валют з НБУ...\n\n";

try {
    $exitCode = $kernel->call('currency:update-rates');

    if ($exitCode === 0) {
        echo "\n✅ Курси успішно оновлено!\n";
    }
} catch (Exception $e) {
    echo '❌ Помилка: '.$e->getMessage()."\n";
}
