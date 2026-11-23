<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->string('bet_code', 24)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('match_id')->constrained('matches')->restrictOnDelete();
            $table->foreignId('market_id')->constrained()->restrictOnDelete();
            $table->foreignId('selection_id')->constrained()->restrictOnDelete();
            $table->string('sport_name', 60)->nullable();
            $table->string('league', 120)->nullable();
            $table->string('match_label', 180);
            $table->string('market_name', 120);
            $table->string('selection_name', 120);
            $table->decimal('odds', 8, 2);
            $table->decimal('stake', 14, 2);
            $table->decimal('potential_return', 14, 2);
            $table->enum('status', ['pending', 'won', 'lost', 'void'])->default('pending');
            $table->decimal('payout', 14, 2)->default(0);
            $table->timestamp('settled_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['match_id', 'status']);
            $table->index(['market_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
