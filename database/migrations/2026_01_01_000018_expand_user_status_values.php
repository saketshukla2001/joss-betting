<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        //
    }
};
