<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HierarchyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $wallet = app(WalletService::class);

        $make = function (array $data) use ($wallet) {
            $user = User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'name' => $data['name'],
                    'email' => $data['username'].'@betzone.local',
                    'reference' => 'Mau NP',
                    'password' => $data['password'],
                    'role' => $data['role'],
                    'status' => $data['status'] ?? 'active',
                    'parent_id' => $data['parent_id'],
                    'agent_code' => $data['agent_code'],
                    'share_percent' => $data['share'],
                    'company_share_percent' => 5,
                    'commission_type' => $data['type'] ?? 'bet_by_bet',
                    'commission_rate' => $data['match'],
                    'session_commission_percent' => $data['session'],
                    'casino_commission_percent' => 2,
                    'matka_commission_percent' => 2,
                    'secure_code' => User::generateSecureCode(),
                ]
            );

            if (($data['chips'] ?? 0) < 0) {
                $user->forceFill(['balance' => (float) $data['chips']])->save();
            } elseif ((float) $user->balance === 0.0 && ($data['chips'] ?? 0) > 0) {
                $wallet->credit($user, (float) $data['chips'], 'admin_credit', 'Opening demo chips');
            }

            $when = Carbon::parse($data['date']);
            $user->timestamps = false;
            $user->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
            $user->timestamps = true;

            return $user;
        };

        $minis = [];
        foreach ([
            ['mini2', 'Mini Admin Two', 'MINI1002', 431700, '2026-03-19', 'active'],
            ['mini3', 'Mini Admin Three', 'MINI1003', -18400, '2026-03-20', 'active'],
            ['mini4', 'Mini Admin Four', 'MINI1004', 211522, '2026-03-23', 'active'],
            ['mini5', 'Mini Admin Five', 'MINI1005', 0, '2026-03-28', 'inactive'],
            ['mini6', 'Mini Admin Six', 'MINI1006', 20000, '2026-04-13', 'active'],
        ] as $index => [$username, $name, $code, $chips, $date, $status]) {
            $minis[] = $make([
                'username' => $username, 'name' => $name, 'password' => 'Agent@12345',
                'role' => 'mini_master', 'parent_id' => $admin->id, 'agent_code' => $code,
                'share' => max(75, 94 - $index), 'match' => 2, 'session' => 3, 'chips' => $chips,
                'status' => $status, 'date' => $date,
            ]);
        }

        $masters = [];
        foreach ([
            ['master2', 'Master Two', 'MAST1002', 0, '2026-03-21', 'active'],
            ['master3', 'Master Three', 'MAST1003', -5200, '2026-03-23', 'active'],
            ['master4', 'Master Four', 'MAST1004', 0, '2026-03-25', 'active'],
            ['master5', 'Master Five', 'MAST1005', 120000, '2026-04-02', 'blocked'],
        ] as $index => [$username, $name, $code, $chips, $date, $status]) {
            $parent = $minis[$index % count($minis)];
            $masters[] = $make([
                'username' => $username, 'name' => $name, 'password' => 'Agent@12345',
                'role' => 'master_master', 'parent_id' => $parent->id, 'agent_code' => $code,
                'share' => 80 - $index, 'match' => 2, 'session' => 3, 'chips' => $chips,
                'status' => $status, 'date' => $date,
            ]);
        }

        $supers = [];
        foreach ([
            ['super2', 'Super Two', 'SUP1002', 118175, '2026-03-19', 'active'],
            ['super3', 'Super Three', 'SUP1003', -9800, '2026-03-21', 'active'],
            ['super4', 'Super Four', 'SUP1004', 256, '2026-03-24', 'active'],
            ['super5', 'Super Five', 'SUP1005', 1740, '2026-03-26', 'inactive'],
        ] as $index => [$username, $name, $code, $chips, $date, $status]) {
            $parent = $masters[$index % count($masters)];
            $supers[] = $make([
                'username' => $username, 'name' => $name, 'password' => 'Agent@12345',
                'role' => 'super_master', 'parent_id' => $parent->id, 'agent_code' => $code,
                'share' => 85 - $index, 'match' => 2, 'session' => 3, 'chips' => $chips,
                'status' => $status, 'date' => $date,
            ]);
        }

        $agents = [];
        foreach ([
            ['agent3', 'Agent Three', 'AG1003', 298707, '2026-03-19', 'active'],
            ['agent4', 'Agent Four', 'AG1004', -4300, '2026-03-21', 'active'],
            ['agent5', 'Agent Five', 'AG1005', 20560, '2026-03-24', 'active'],
        ] as $index => [$username, $name, $code, $chips, $date, $status]) {
            $parent = $supers[$index % count($supers)];
            $agents[] = $make([
                'username' => $username, 'name' => $name, 'password' => 'Agent@12345',
                'role' => 'agent', 'parent_id' => $parent->id, 'agent_code' => $code,
                'share' => 70 - $index, 'match' => 2, 'session' => 3, 'chips' => $chips,
                'status' => $status, 'date' => $date,
            ]);
        }

        $allAgents = User::where('role', 'agent')->get();

        foreach ([
            ['client6', 'Client Six', 'CL6001', 90, '2026-03-20', 'active'],
            ['client7', 'Client Seven', 'CL6002', -150, '2026-03-21', 'active'],
            ['client8', 'Client Eight', 'CL6003', 0.65, '2026-03-22', 'active'],
            ['client9', 'Client Nine', 'CL6004', 11.70, '2026-03-24', 'inactive'],
            ['client10', 'Client Ten', 'CL6005', 61.50, '2026-03-25', 'active'],
        ] as $index => [$username, $name, $code, $chips, $date, $status]) {
            $parent = $allAgents[$index % count($allAgents)];
            $make([
                'username' => $username, 'name' => $name, 'password' => 'Client@12345',
                'role' => 'client', 'parent_id' => $parent->id, 'agent_code' => $code,
                'share' => 0, 'match' => 0, 'session' => 0, 'chips' => $chips,
                'status' => $status, 'date' => $date,
            ]);
        }

        foreach (['agent1', 'agent2'] as $agentUsername) {
            $demoAgent = User::where('username', $agentUsername)->first();
            if ($demoAgent && (float) $demoAgent->balance === 0.0) {
                $wallet->credit($demoAgent, 50000, 'admin_credit', 'Opening demo betting chips');
            }
        }

        User::where('role', 'client')->update([
            'share_percent' => 0,
            'company_share_percent' => 0,
            'commission_type' => 'net_loss',
            'commission_rate' => 0,
            'session_commission_percent' => 0,
            'casino_commission_percent' => 0,
            'matka_commission_percent' => 0,
        ]);

        $dateMap = [
            'mini1' => '2026-03-19', 'master1' => '2026-03-19', 'super1' => '2026-03-19',
            'agent1' => '2026-03-19', 'agent2' => '2026-03-20',
            'demo' => '2026-03-19', 'rahul' => '2026-03-20', 'priya' => '2026-03-23',
            'vikram' => '2026-03-24', 'karan' => '2026-03-28',
        ];

        foreach ($dateMap as $username => $date) {
            $user = User::where('username', $username)->first();
            if (! $user) continue;
            $user->timestamps = false;
            $user->forceFill(['created_at' => Carbon::parse($date), 'updated_at' => Carbon::parse($date)])->save();
            $user->timestamps = true;
        }

        User::where('role', 'client')->where('agent_code', 'like', 'CL%')->get()->each(function (User $user) {
            $new = 'C'.substr($user->agent_code, 2);
            if (! User::where('agent_code', $new)->exists()) {
                $user->update(['agent_code' => $new]);
            }
        });
    }
}
