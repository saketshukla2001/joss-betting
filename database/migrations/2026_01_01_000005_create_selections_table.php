<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('result_key', 20)->nullable();
            $table->decimal('odds', 8, 2)->default(2.00);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->enum('result', ['win', 'lose', 'void'])->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['market_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selections');
    }
};
