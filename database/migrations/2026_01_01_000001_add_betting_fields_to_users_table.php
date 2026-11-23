<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 32)->unique()->after('name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('role', 20)->default('client')->after('password');
            $table->string('status', 20)->default('active')->after('role');
            $table->decimal('balance', 14, 2)->default(0)->after('status');
            $table->decimal('exposure', 14, 2)->default(0)->after('balance');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn(['username', 'phone', 'role', 'status', 'balance', 'exposure', 'last_login_at']);
        });
    }
};
