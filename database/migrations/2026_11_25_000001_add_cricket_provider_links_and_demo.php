<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cricket_provider_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('provider_match_id', 40);
            $table->timestamps();

            $table->unique(['provider', 'provider_match_id'], 'cricket_provider_link_unique');
        });

        Schema::create('cricket_demo_matches', function (Blueprint $table) {
            $table->id();
            $table->string('team1', 120);
            $table->string('team2', 120);
            $table->string('batting', 120);
            $table->string('status', 20)->default('ready');
            $table->unsignedTinyInteger('innings')->default(1);
            $table->unsignedInteger('runs')->default(0);
            $table->unsignedTinyInteger('wickets')->default(0);
            $table->unsignedInteger('legal_balls')->default(0);
            $table->boolean('auto_bowl')->default(false);
            $table->timestamps();
        });

        Schema::create('cricket_demo_balls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cricket_demo_match_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('seq');
            $table->string('kind', 12);
            $table->unsignedTinyInteger('runs')->default(0);
            $table->boolean('legal')->default(true);
            $table->boolean('wicket')->default(false);
            $table->timestamps();

            $table->unique(['cricket_demo_match_id', 'seq']);
        });

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('name', 'cricket_ai_approval_required')->update(['value' => '0']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cricket_demo_balls');
        Schema::dropIfExists('cricket_demo_matches');
        Schema::dropIfExists('cricket_provider_links');
    }
};
