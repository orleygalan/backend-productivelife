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
        Schema::create('daily_completions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('goal_task_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            // Fecha del dia que se completo (sin hora)
            $table->date('completed_date');
            $table->integer('xp_earned');
            $table->timestamps();

            // Un usuario no puede completar la misma tarea dos veces el mismo día
            $table->unique(['goal_task_id', 'user_id', 'completed_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_completions');
    }
};
