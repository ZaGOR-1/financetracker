<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = DB::table('exchange_rates')->count();
echo "Курсів у БД: {$count}\n\n";

if ($count > 0) {
    DB::table('exchange_rates')->orderBy('date', 'desc')->get()->each(function ($r) {
        echo "{$r->base_currency} -> {$r->target_currency}: ".number_format($r->rate, 6)." ({$r->date})\n";
    });
} else {
    echo "❌ Таблиця порожня\n";
}
