<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->string('provider', 30)->nullable()->after('featured');
            $table->string('provider_match_id', 40)->nullable()->after('provider');
            $table->unique(['provider', 'provider_match_id'], 'matches_provider_match_unique');
        });

        Schema::create('cricket_market_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider', 30)->default('cricbuzz');
            $table->string('provider_match_id', 40);
            $table->string('fingerprint', 190)->unique();
            $table->string('template', 40);
            $table->string('batting_team', 120);
            $table->unsignedTinyInteger('innings');
            $table->unsignedSmallInteger('target_over');
            $table->unsignedTinyInteger('target_ball')->nullable();
            $table->unsignedInteger('threshold')->nullable();
            $table->string('trigger_event', 40);
            $table->string('close_rule', 60);
            $table->string('resolve_rule', 60);
            $table->string('source', 20);
            $table->string('phase', 20)->default('open');
            $table->json('parameters');
            $table->json('snapshot')->nullable();
            $table->json('generation_meta')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['provider_match_id', 'phase']);
        });

        Schema::create('cricket_market_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cricket_market_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_match_id', 40)->nullable();
            $table->string('event', 30);
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['provider_match_id', 'created_at']);
        });

        Schema::create('cricket_question_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->default('cricbuzz');
            $table->string('provider_match_id', 40);
            $table->string('fingerprint', 190)->unique();
            $table->string('template', 40);
            $table->string('question', 180);
            $table->json('parameters');
            $table->string('source', 20);
            $table->string('status', 20)->default('pending');
            $table->foreignId('market_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rejection_reason', 180)->nullable();
            $table->timestamps();
        });

        Schema::create('cricket_match_controls', function (Blueprint $table) {
            $table->id();
            $table->string('provider_match_id', 40)->unique();
            $table->boolean('generation_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cricket_match_controls');
        Schema::dropIfExists('cricket_question_candidates');
        Schema::dropIfExists('cricket_market_events');
        Schema::dropIfExists('cricket_market_rules');

        Schema::table('matches', function (Blueprint $table) {
            $table->dropUnique('matches_provider_match_unique');
            $table->dropColumn(['provider', 'provider_match_id']);
        });
    }
};
