<?php

namespace App\Support;

use App\Models\User;

class Partnership
{
    public const LEVELS = [
        'admin' => ['key' => 'admin_master', 'label' => 'Admin Master'],
        'mini_master' => ['key' => 'mini_master', 'label' => 'Mini Master'],
        'master_master' => ['key' => 'master_master', 'label' => 'Master Master'],
        'super_master' => ['key' => 'super_master', 'label' => 'Super Master'],
        'agent' => ['key' => 'agent_master', 'label' => 'Agent Master'],
        'client' => ['key' => 'client_master', 'label' => 'Client Master'],
    ];

    public static function for(User $user): array
    {
        $chain = [];
        $node = $user;

        while ($node) {
            $chain[$node->role] = $node;
            $node = $node->parent;
        }

        $shares = [];
        $match = [];
        $selfKey = self::LEVELS[$user->role]['key'] ?? null;
        $session = [];
        $casino = [];
        $matka = [];

        foreach (self::LEVELS as $role => $meta) {
            $key = $meta['key'];
            $member = $chain[$role] ?? null;

            $shares[$key] = $selfKey === $key ? round((float) $user->share_percent, 2) : 0.0;

            if ($role === 'client') {
                $match[$key] = 0.0;
                $session[$key] = 0.0;
                $casino[$key] = 0.0;
                $matka[$key] = 0.0;
                continue;
            }

            $match[$key] = round((float) ($member?->commission_rate ?? $user->commission_rate), 2);
            $session[$key] = round((float) ($member?->session_commission_percent ?? $user->session_commission_percent), 2);
            $casino[$key] = round((float) ($member?->casino_commission_percent ?? $user->casino_commission_percent), 2);
            $matka[$key] = round((float) ($member?->matka_commission_percent ?? $user->matka_commission_percent), 2);
        }

        return [
            'title' => strtoupper($user->username).' PARTNERSHIP DETAILS',
            'match_share' => $shares,
            'match_commission' => $match,
            'session_commission' => $session,
            'casino_share' => $shares,
            'casino_commission' => $casino,
            'matka_share' => $shares,
            'matka_commission' => $matka,
        ];
    }
}
