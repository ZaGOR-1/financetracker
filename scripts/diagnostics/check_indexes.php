<?php

/**
 * Скрипт для перевірки індексів в базі даних
 * Використання: php scripts/diagnostics/check_indexes.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "====================================\n";
echo "  Перевірка індексів БД\n";
echo "====================================\n\n";

$tables = ['transactions', 'categories', 'budgets', 'users'];

foreach ($tables as $table) {
    if (!Schema::hasTable($table)) {
        echo "⚠️  Таблиця '{$table}' не існує\n\n";
        continue;
    }
    
    echo "📊 Таблиця: {$table}\n";
    echo str_repeat('-', 50) . "\n";
    
    $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='{$table}'");
    
    if (empty($indexes)) {
        echo "   Індексів не знайдено\n";
    } else {
        foreach ($indexes as $index) {
            // Отримуємо деталі індексу
            $indexInfo = DB::select("PRAGMA index_info('{$index->name}')");
            $columns = array_map(fn($col) => $col->name, $indexInfo);
            
            echo "   ✓ {$index->name}\n";
            echo "     Колонки: " . implode(', ', $columns) . "\n";
        }
    }
    
    echo "\n";
}

echo "====================================\n";
echo "✅ Перевірка завершена!\n";
echo "====================================\n";
