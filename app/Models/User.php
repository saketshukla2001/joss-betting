<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'agent_code',
        'parent_id',
        'email',
        'phone',
        'reference',
        'password',
        'role',
        'status',
        'balance',
        'exposure',
        'commission_type',
        'commission_rate',
        'commission_balance',
        'share_percent',
        'company_share_percent',
        'session_commission_percent',
        'casino_commission_percent',
        'matka_commission_percent',
        'secure_code',
        'panel_permissions',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'float',
            'exposure' => 'float',
            'commission_rate' => 'float',
            'commission_balance' => 'float',
            'share_percent' => 'float',
            'company_share_percent' => 'float',
            'session_commission_percent' => 'float',
            'casino_commission_percent' => 'float',
            'matka_commission_percent' => 'float',
            'panel_permissions' => 'array',
        ];
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function players()
    {
        return $this->hasMany(User::class, 'parent_id')->where('role', 'client');
    }

    public function agentCommissions()
    {
        return $this->hasMany(AgentCommission::class, 'agent_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isMasterPanelUser(): bool
    {
        return in_array($this->role, ['mini_master', 'master_master', 'super_master', 'agent'], true);
    }

    public function canEarnCommission(): bool
    {
        return in_array($this->role, ['admin', 'mini_master', 'master_master', 'super_master', 'agent'], true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)->explode(' ')->filter()->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeAgents($query)
    {
        return $query->where('role', 'agent');
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Main Super Admin',
            'mini_master' => 'Mini Master',
            'master_master' => 'Master Master',
            'super_master' => 'Super Master',
            'agent' => 'Agent Master',
            'client' => 'Client Master',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    public static function generateSecureCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (static::where('secure_code', $code)->exists());

        return $code;
    }

    /**
     * Every descendant id (agents and clients), not just players.
     *
     * @return array<int>
     */
    public const PANEL_MODULES = [
        'network' => ['label' => 'My Network', 'description' => 'View full downline members and hierarchy.'],
        'create' => ['label' => 'Create Downline', 'description' => 'Create the next level account.'],
        'matches' => ['label' => 'Matches / Betting', 'description' => 'View match details and place bets.'],
        'mybets' => ['label' => 'My Bets', 'description' => 'View your own bet tickets.'],
        'players' => ['label' => 'My Players', 'description' => 'View players across the downline.'],
        'bets' => ['label' => 'Downline Bets', 'description' => 'View bets placed by downline players.'],
        'commissions' => ['label' => 'Commission', 'description' => 'View own commission ledger and balance.'],
        'wallet' => ['label' => 'Wallet', 'description' => 'View wallet and request withdrawals.'],
        'account' => ['label' => 'My Profile', 'description' => 'Edit profile and password.'],
    ];

    public function defaultPanelPermissions(): array
    {
        if (! $this->isMasterPanelUser()) {
            return [];
        }

        return array_keys(self::PANEL_MODULES);
    }

    public function effectivePanelPermissions(): array
    {
        if ($this->isAdmin()) {
            return array_keys(self::PANEL_MODULES);
        }

        if (! $this->isMasterPanelUser()) {
            return [];
        }

        return is_array($this->panel_permissions)
            ? array_values(array_intersect($this->panel_permissions, array_keys(self::PANEL_MODULES)))
            : $this->defaultPanelPermissions();
    }

    public function hasPanelModule(string $module): bool
    {
        return in_array($module, $this->effectivePanelPermissions(), true);
    }

    public function downlineIds(): array
    {
        $ids = [];
        $queue = [$this->id];
        $seen = [];

        while ($queue) {
            $parentId = array_shift($queue);

            if (isset($seen[$parentId]) || count($seen) > 1000) {
                continue;
            }

            $seen[$parentId] = true;

            foreach (User::where('parent_id', $parentId)->get(['id']) as $child) {
                $ids[] = $child->id;
                $queue[] = $child->id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Every client id below this agent, across unlimited sub-agent levels.
     *
     * @return array<int>
     */
    public function downlinePlayerIds(): array
    {
        $playerIds = [];
        $queue = [$this->id];
        $seen = [];

        while ($queue) {
            $parentId = array_shift($queue);

            if (isset($seen[$parentId]) || count($seen) > 500) {
                continue;
            }

            $seen[$parentId] = true;

            $children = User::where('parent_id', $parentId)->get(['id', 'role']);

            foreach ($children as $child) {
                if ($child->role === 'client') {
                    $playerIds[] = $child->id;
                } elseif ($child->role === 'agent') {
                    $queue[] = $child->id;
                }
            }
        }

        return array_values(array_unique($playerIds));
    }

    /**
     * Agent ancestors starting with the immediate parent.
     * Guards against accidental cycles in manually edited data.
     */
    public function agentAncestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;
        $guard = 0;

        while ($parent && $guard < 12) {
            if ($parent->canEarnCommission()) {
                $ancestors[] = $parent;
            }

            $parent = $parent->parent;
            $guard++;
        }

        return $ancestors;
    }
}
