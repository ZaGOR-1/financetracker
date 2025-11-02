<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

echo 'Поточний timezone в config: '.config('app.timezone')."\n";
echo 'Поточний час сервера: '.now()->format('Y-m-d H:i:s')."\n\n";

$transactions = Transaction::orderBy('transaction_date', 'desc')->take(5)->get();

foreach ($transactions as $t) {
    echo "ID: {$t->id}\n";
    echo "  Опис: {$t->description}\n";
    echo "  В БД (raw): {$t->getRawOriginal('transaction_date')}\n";
    echo "  Carbon object: {$t->transaction_date}\n";
    echo "  Форматовано: {$t->transaction_date->format('d.m.Y H:i:s')}\n";
    echo "  Timezone: {$t->transaction_date->timezone}\n";
    echo "\n";
}
