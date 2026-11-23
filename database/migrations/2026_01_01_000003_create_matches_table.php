<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->restrictOnDelete();
            $table->string('league', 120);
            $table->string('home_team', 120);
            $table->string('away_team', 120);
            $table->dateTime('start_time');
            $table->enum('status', ['upcoming', 'live', 'settled', 'cancelled'])->default('upcoming');
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->enum('result', ['home', 'away', 'draw'])->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamps();

            $table->index(['status', 'start_time']);
            $table->index('featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
