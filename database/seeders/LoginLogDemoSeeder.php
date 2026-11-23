<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoginLogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $rows = [];

        foreach ($users as $index => $user) {
            for ($i = 0; $i < 3; $i++) {
                $when = now()->subDays(($index + 1) * 2 + $i)->setTime(8 + $i, 10 + $index, 0);
                $rows[] = [
                    'user_id' => $user->id,
                    'ip_address' => '103.21.'.(20 + $index).'.'.(10 + $i),
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36',
                    'created_at' => $when,
                    'updated_at' => $when,
                ];
            }
        }

        DB::table('login_logs')->insert($rows);
    }
}
