<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            // Puede ser null si es un gasto en recompensa
            $table->foreignUuid('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('goal_task_id')->nullable()->constrained()->nullOnDelete();
            // Positivo = ganancia, Negativo = gasto
            $table->integer('amount');
            $table->enum('type', ['daily_task', 'streak_bonus', 'reward_redeem']);
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_logs');
    }
};
