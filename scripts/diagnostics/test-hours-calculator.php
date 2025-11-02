<?php

/**
 * Тест калькулятора годин
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HourCalculation;
use App\Models\User;

echo "═══════════════════════════════════════════════════\n";
echo "🧮 Тест калькулятора годин\n";
echo "═══════════════════════════════════════════════════\n\n";

$user = User::first();

if (! $user) {
    echo "❌ Користувача не знайдено!\n";
    exit(1);
}

echo "👤 Користувач: {$user->name}\n\n";

// Створюємо тестовий розрахунок
echo "📝 Створюємо тестовий розрахунок...\n";

$calculation = HourCalculation::create([
    'user_id' => $user->id,
    'hours' => 8,
    'hourly_rate' => 25,
    'currency' => 'USD',
    'name' => 'Тестовий проект',
]);

echo "✅ Розрахунок створено!\n\n";

echo "═══════════════════════════════════════════════════\n";
echo "📊 Результати розрахунку:\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "⏱️  Години на день: {$calculation->hours}\n";
echo "💵 Ставка за годину: {$calculation->currency_symbol}{$calculation->hourly_rate}\n";
echo "💱 Валюта: {$calculation->currency}\n";
echo "🏷️  Назва: {$calculation->name}\n\n";

echo "───────────────────────────────────────────────────\n";
echo "💰 Заробіток:\n";
echo "───────────────────────────────────────────────────\n\n";

echo "За день:    {$calculation->formatted_daily_salary}\n";
echo "За тиждень: {$calculation->formatted_weekly_salary}\n";
echo "За місяць:  {$calculation->formatted_monthly_salary}\n";
echo "За рік:     {$calculation->formatted_yearly_salary}\n";

echo "\n═══════════════════════════════════════════════════\n";
echo "🔢 Математична перевірка:\n";
echo "═══════════════════════════════════════════════════\n\n";

$daily = $calculation->hours * $calculation->hourly_rate;
$weekly = $daily * 5;
$monthly = $daily * 21.67;
$yearly = $daily * 260;

echo "Денний:   {$calculation->hours} × {$calculation->hourly_rate} = {$daily}\n";
echo "Тижневий: {$daily} × 5 = {$weekly}\n";
echo "Місячний: {$daily} × 21.67 = {$monthly}\n";
echo "Річний:   {$daily} × 260 = {$yearly}\n";

echo "\n═══════════════════════════════════════════════════\n";
echo "📋 Всі розрахунки користувача:\n";
echo "═══════════════════════════════════════════════════\n\n";

$allCalculations = $user->hourCalculations()->latest()->get();

foreach ($allCalculations as $calc) {
    echo sprintf(
        "• %s | %s год × %s = %s/міс\n",
        $calc->created_at->format('d.m.Y H:i'),
        $calc->hours,
        $calc->currency_symbol.$calc->hourly_rate,
        $calc->formatted_monthly_salary
    );
}

echo "\n✅ Всього розрахунків: ".$allCalculations->count()."\n";

echo "\n═══════════════════════════════════════════════════\n";
echo "🗑️  Видаляємо тестовий розрахунок...\n";
echo "═══════════════════════════════════════════════════\n\n";

$calculation->delete();
echo "✅ Тестовий розрахунок видалено!\n";

echo "\n═══════════════════════════════════════════════════\n";
echo "✅ Тест завершено успішно!\n";
echo "═══════════════════════════════════════════════════\n";
