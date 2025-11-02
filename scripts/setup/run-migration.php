<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Запуск міграції для додавання підтримки валют...\n\n";

try {
    $exitCode = $kernel->call('migrate');

    if ($exitCode === 0) {
        echo "\n✅ Міграцію успішно виконано!\n";
    } else {
        echo "\n❌ Помилка міграції\n";
    }
} catch (Exception $e) {
    echo '❌ Помилка: '.$e->getMessage()."\n";
}
