<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->default('cricbuzz');
            $table->string('provider_match_id', 40)->nullable();
            $table->foreignId('match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->string('match_label', 180)->nullable();
            $table->string('name', 120);
            $table->json('selections')->nullable();
            $table->enum('status', ['draft', 'review', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_match_id', 'name'], 'market_drafts_provider_match_name_unique');
            $table->index(['status', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_drafts');
    }
};
