<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('name', 120);
            $table->boolean('auto_settle')->default(false);
            $table->enum('status', ['open', 'suspended', 'settled', 'void'])->default('open');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['match_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
