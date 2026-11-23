<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('reference', 120)->nullable()->after('phone');
            $table->decimal('casino_commission_percent', 6, 2)->default(2)->after('session_commission_percent');
            $table->decimal('matka_commission_percent', 6, 2)->default(2)->after('casino_commission_percent');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reference', 'casino_commission_percent', 'matka_commission_percent']);
        });
    }
};
