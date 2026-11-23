<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bet_id')->constrained('bets')->cascadeOnDelete();
            $table->decimal('stake', 14, 2);
            $table->decimal('rate', 6, 2);
            $table->string('basis', 20)->default('turnover');
            $table->decimal('amount', 14, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->string('payout_reference', 60)->nullable();
            $table->timestamps();

            $table->unique(['agent_id', 'bet_id']);
            $table->index(['agent_id', 'status']);
            $table->index(['player_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
    }
};
