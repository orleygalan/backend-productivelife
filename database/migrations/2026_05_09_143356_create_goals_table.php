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
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            // Se calcula automaticamente segun end_date - start_date
            $table->enum('term', ['short', 'medium', 'long']);
            $table->enum('status', ['active', 'completed', 'failed'])->default('active');
            // Racha actual de dias consecutivos
            $table->integer('current_streak')->default(0);
            // Maxima racha alcanzada
            $table->integer('max_streak')->default(0);
            // Dias fallados es decir que no completo ninguna tarea
            $table->integer('missed_days')->default(0);
            // Si ya se otorgo el bonus de completar la meta
            $table->boolean('bonus_granted')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
