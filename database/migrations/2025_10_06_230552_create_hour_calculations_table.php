<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hour_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('hours', 8, 2)->comment('Кількість годин на день');
            $table->decimal('hourly_rate', 10, 2)->comment('Ставка за годину');
            $table->string('currency', 3)->default('UAH'); // UAH, USD, PLN
            $table->string('name')->nullable()->comment('Назва розрахунку (необов\'язково)');
            $table->timestamps();
            
            // Індекси
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hour_calculations');
    }
};
