<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\CommissionService;
use App\Services\WalletService;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $wallet = app(WalletService::class);

        $admin = User::where('username', 'admin')->firstOrFail();
        $admin->fill([
            'name' => 'MAU NP',
            'reference' => 'Mau NP',
            'agent_code' => 'SAD88852',
            'share_percent' => 95,
            'company_share_percent' => 5,
            'commission_rate' => 2,
            'session_commission_percent' => 3,
            'casino_commission_percent' => 2,
            'matka_commission_percent' => 2,
            'secure_code' => $admin->secure_code ?: User::generateSecureCode(),
        ])->save();

        if ((float) $admin->balance < 2199109) {
            $wallet->credit(
                $admin,
                2199109 - (float) $admin->balance,
                'admin_credit',
                'Opening admin coin balance'
            );
        }

        $mini = $this->account('mini1', 'Mini Master One', 'mini_master', $admin->id, [
            'agent_code' => 'MINI1001',
            'share_percent' => 90,
            'company_share_percent' => 5,
            'commission_rate' => 2.5,
            'session_commission_percent' => 2.5,
            'balance' => 250000,
        ], $wallet);

        $master = $this->account('master1', 'Master Master One', 'master_master', $mini->id, [
            'agent_code' => 'MAST1001',
            'share_percent' => 85,
            'company_share_percent' => 5,
            'commission_rate' => 2.5,
            'session_commission_percent' => 2.5,
            'balance' => 150000,
        ], $wallet);

        $super = $this->account('super1', 'Super Master One', 'super_master', $master->id, [
            'agent_code' => 'SUP1001',
            'share_percent' => 80,
            'company_share_percent' => 5,
            'commission_rate' => 3,
            'session_commission_percent' => 2,
            'balance' => 100000,
        ], $wallet);

        $agent1 = User::updateOrCreate(
            ['username' => 'agent1'],
            [
                'name' => 'Rohit Malhotra',
                'email' => 'agent1@betzone.test',
                'phone' => '9866778899',
                'password' => 'Agent@12345',
                'role' => 'agent',
                'status' => 'active',
                'parent_id' => $super->id,
                'agent_code' => 'AG1001',
                'commission_rate' => 5,
                'commission_type' => 'turnover',
                'share_percent' => 75,
                'company_share_percent' => 5,
                'session_commission_percent' => 2.5,
                'secure_code' => User::generateSecureCode(),
            ]
        );

        $agent2 = User::updateOrCreate(
            ['username' => 'agent2'],
            [
                'name' => 'Neha Kapoor',
                'email' => 'agent2@betzone.test',
                'phone' => '9877889900',
                'password' => 'Agent2@12345',
                'role' => 'agent',
                'status' => 'active',
                'parent_id' => $agent1->id,
                'agent_code' => 'AG1002',
                'commission_rate' => 3,
                'commission_type' => 'net_loss',
                'share_percent' => 70,
                'company_share_percent' => 5,
                'session_commission_percent' => 2,
                'secure_code' => User::generateSecureCode(),
            ]
        );

        User::whereIn('username', ['demo', 'rahul'])->update(['parent_id' => $agent1->id]);
        User::whereIn('username', ['priya', 'vikram'])->update(['parent_id' => $agent2->id]);
        User::where('username', 'karan')->update(['parent_id' => $agent1->id]);

        User::whereNull('secure_code')->get()->each(function (User $user) {
            $user->update(['secure_code' => User::generateSecureCode()]);
        });

        User::where('role', 'client')->whereNull('agent_code')->get()->each(function (User $user) {
            do {
                $code = 'CL'.random_int(1000, 9999);
            } while (User::where('agent_code', $code)->exists());

            $user->update(['agent_code' => $code]);
        });

        app(CommissionService::class)->backfill();
    }

    private function account(string $username, string $name, string $role, int $parentId, array $attributes, WalletService $wallet): User
    {
        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $username.'@betzone.test',
                'password' => 'Agent@12345',
                'role' => $role,
                'status' => 'active',
                'parent_id' => $parentId,
            ]
        );

        $openingBalance = (float) ($attributes['balance'] ?? 0);
        unset($attributes['balance']);

        $user->fill($attributes + [
            'name' => $name,
            'role' => $role,
            'status' => 'active',
            'parent_id' => $parentId,
            'secure_code' => $user->secure_code ?: User::generateSecureCode(),
        ])->save();

        if ((float) $user->balance <= 0 && $openingBalance > 0) {
            $wallet->credit($user, $openingBalance, 'admin_credit', 'Opening coin balance');
        }

        return $user;
    }
}
