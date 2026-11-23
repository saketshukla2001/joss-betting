<?php

namespace App\Support;

class Hierarchy
{
    public const LEVELS = [
        'mini_master' => [
            'label' => 'Mini Master',
            'form_label' => 'Mini Admin',
            'create_title' => 'CREATE MINI ADMIN',
            'details_title' => 'MINI ADMIN DETAILS',
            'parent_label' => 'ADMIN',
            'role' => 'mini_master',
            'parents' => ['admin'],
            'prefix' => 'MINI',
        ],
        'master_master' => [
            'label' => 'Master Master',
            'form_label' => 'Master',
            'create_title' => 'CREATE MASTER',
            'details_title' => 'MASTER DETAILS',
            'parent_label' => 'MINI',
            'role' => 'master_master',
            'parents' => ['admin', 'mini_master'],
            'prefix' => 'MAST',
        ],
        'super_master' => [
            'label' => 'Super Master',
            'form_label' => 'Super Master',
            'create_title' => 'CREATE SUPER MASTER',
            'details_title' => 'SUPER MASTER DETAILS',
            'parent_label' => 'MASTER',
            'role' => 'super_master',
            'parents' => ['admin', 'mini_master', 'master_master'],
            'prefix' => 'SUP',
        ],
        'agent_master' => [
            'label' => 'Agent Master',
            'form_label' => 'Agent',
            'create_title' => 'CREATE AGENT',
            'details_title' => 'AGENT DETAILS',
            'parent_label' => 'SUPER',
            'role' => 'agent',
            'parents' => ['admin', 'mini_master', 'master_master', 'super_master'],
            'prefix' => 'AG',
        ],
        'client_master' => [
            'label' => 'Client Master',
            'form_label' => 'Client',
            'create_title' => 'CREATE CLIENT',
            'details_title' => 'CLIENT DETAILS',
            'parent_label' => 'AGENT',
            'role' => 'client',
            'parents' => ['admin', 'mini_master', 'master_master', 'super_master', 'agent'],
            'prefix' => 'CL',
        ],
    ];

    public const SEQUENCE = [
        'admin' => 'mini_master',
        'mini_master' => 'master_master',
        'master_master' => 'super_master',
        'super_master' => 'agent_master',
        'agent' => 'client_master',
        'client' => null,
    ];

    public static function nextLevelFor(string $role): ?string
    {
        return self::SEQUENCE[$role] ?? null;
    }

    public static function get(string $level): array
    {
        abort_unless(isset(self::LEVELS[$level]), 404);

        return self::LEVELS[$level];
    }

    public static function label(string $level): string
    {
        return self::get($level)['label'];
    }

    public static function role(string $level): string
    {
        return self::get($level)['role'];
    }

    public static function all(): array
    {
        return self::LEVELS;
    }
}
