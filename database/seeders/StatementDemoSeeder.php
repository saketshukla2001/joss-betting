<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatementDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $targets = User::where('id', '!=', $admin->id)->get();

        DB::table('wallet_transactions')->where('reference_type', 'demo_statement')->delete();

        $rows = [];

        for ($i = 0; $i < 22; $i++) {
            $target = $targets->random();
            $credit = $i % 3 !== 1;
            $amount = random_int(25, 400) * 1000;
            $signed = $credit ? $amount : -$amount;
            $balance = random_int(2200000, 6200000);

            $when = now()->subDays($i * 3 + random_int(0, 2))->setTime(random_int(9, 20), random_int(0, 59));

            $description = 'COINS '.($credit ? 'CREDITED TO' : 'DEBITED FROM').' '.Str::upper($target->username)
                .' ('.($target->agent_code ?: 'NA').') BY '.$admin->name.' ('.$admin->agent_code.') ['.$admin->agent_code.','.$admin->name.']';

            $rows[] = [
                'user_id' => $admin->id,
                'type' => $credit ? 'admin_credit' : 'admin_debit',
                'amount' => $signed,
                'balance_after' => $balance,
                'status' => 'completed',
                'is_demo' => true,
                'reference_type' => 'demo_statement',
                'reference_id' => null,
                'description' => $description,
                'meta' => json_encode(['demo' => true, 'target' => $target->username]),
                'created_at' => $when,
                'updated_at' => $when,
            ];

        }

        DB::table('wallet_transactions')->insert($rows);
    }
}
