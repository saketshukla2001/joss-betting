<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'client'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id')->index();
            $table->string('agent_code', 24)->nullable()->after('username')->index();
            $table->decimal('commission_balance', 14, 2)->default(0)->after('agent_code');
            $table->string('commission_type', 20)->default('turnover')->after('commission_balance');
            $table->decimal('commission_rate', 6, 2)->default(0)->after('commission_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'agent_code', 'commission_type', 'commission_rate', 'commission_balance']);
        });
    }
};
