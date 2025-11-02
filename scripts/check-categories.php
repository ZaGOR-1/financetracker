#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "═══════════════════════════════════════════════════════════════\n";
echo "📊 Категорії в базі даних\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$incomeCategories = Category::where('type', 'income')->whereNull('user_id')->get();
$expenseCategories = Category::where('type', 'expense')->whereNull('user_id')->get();

echo '💰 ДОХОДИ ('.$incomeCategories->count()."):\n";
echo "─────────────────────────────────────────────────────────────\n";
foreach ($incomeCategories as $cat) {
    echo sprintf("  %-3d %-20s %-10s %s\n", $cat->id, $cat->name, $cat->icon, $cat->color);
}

echo "\n💸 ВИТРАТИ (".$expenseCategories->count()."):\n";
echo "─────────────────────────────────────────────────────────────\n";
foreach ($expenseCategories as $cat) {
    echo sprintf("  %-3d %-20s %-10s %s\n", $cat->id, $cat->name, $cat->icon, $cat->color);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo 'Всього категорій: '.Category::count()."\n";
echo "═══════════════════════════════════════════════════════════════\n";
