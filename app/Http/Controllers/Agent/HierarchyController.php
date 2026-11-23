<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use App\Models\User;
use App\Services\WalletService;
use App\Support\Hierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HierarchyController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();
        $nextLevel = Hierarchy::nextLevelFor($actor->role);
        abort_if($nextLevel === null, 403, 'This account cannot create a downline.');

        $config = Hierarchy::get($nextLevel);
        $children = User::where('parent_id', $actor->id)
            ->where('role', $config['role'])
            ->withCount('children')
            ->orderByDesc('created_at')
            ->get();

        $chain = [];
        $node = $actor;
        while ($node) {
            array_unshift($chain, $node);
            $node = $node->parent;
        }

        $downline = User::whereIn('id', $actor->downlineIds())
            ->with('parent')
            ->orderByRaw("CASE role WHEN 'mini_master' THEN 1 WHEN 'master_master' THEN 2 WHEN 'super_master' THEN 3 WHEN 'agent' THEN 4 WHEN 'client' THEN 5 ELSE 6 END")
            ->orderBy('username')
            ->get();

        $stats = [
            'children' => $children->count(),
            'active' => $children->where('status', 'active')->count(),
            'coins' => (float) $downline->sum('balance'),
            'pending_commission' => (float) $actor->agentCommissions()->where('status', 'pending')->sum('amount'),
            'downline' => $downline->count(),
        ];

        return view('agent.hierarchy.index', compact('actor', 'nextLevel', 'config', 'children', 'chain', 'stats', 'downline'));
    }

    public function create(Request $request)
    {
        $actor = $request->user();
        $nextLevel = Hierarchy::nextLevelFor($actor->role);
        abort_if($nextLevel === null, 403);

        $config = Hierarchy::get($nextLevel);

        return view('agent.hierarchy.form', [
            'actor' => $actor,
            'nextLevel' => $nextLevel,
            'config' => $config,
            'suggestedPassword' => $this->suggestedPassword(),
        ]);
    }

    public function store(Request $request, WalletService $wallet)
    {
        $actor = $request->user();
        $nextLevel = Hierarchy::nextLevelFor($actor->role);
        abort_if($nextLevel === null, 403);

        $config = Hierarchy::get($nextLevel);
        $isClient = $config['role'] === 'client';

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'reference' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:5', 'max:20'],
            'coins' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'my_share_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'my_commission_type' => ['nullable', 'in:bet_by_bet,net_loss'],
            'my_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'my_session_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'my_casino_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'my_matka_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'share_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'commission_type' => [$isClient ? 'nullable' : 'required', 'in:bet_by_bet,net_loss'],
            'commission_rate' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'session_commission_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'casino_commission_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'matka_commission_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
        ]);

        $myShare = round((float) ($data['my_share_percent'] ?? $actor->share_percent), 2);
        $myType = $data['my_commission_type'] ?? ($actor->commission_type === 'net_loss' ? 'net_loss' : 'bet_by_bet');
        $myMatch = round((float) ($data['my_commission_rate'] ?? $actor->commission_rate), 2);
        $mySession = round((float) ($data['my_session_commission_percent'] ?? $actor->session_commission_percent), 2);
        $myCasino = round((float) ($data['my_casino_commission_percent'] ?? $actor->casino_commission_percent), 2);
        $myMatka = round((float) ($data['my_matka_commission_percent'] ?? $actor->matka_commission_percent), 2);

        $coins = round((float) ($data['coins'] ?? 0), 2);

        if ($coins > (float) $actor->balance) {
            throw ValidationException::withMessages(['coins' => 'Opening coins cannot exceed your available balance.']);
        }

        if (! $isClient) {
            $share = round((float) $data['share_percent'], 2);
            $match = round((float) $data['commission_rate'], 2);
            $session = round((float) $data['session_commission_percent'], 2);
            $casino = round((float) $data['casino_commission_percent'], 2);
            $matka = round((float) $data['matka_commission_percent'], 2);

            if ($share > $myShare) {
                throw ValidationException::withMessages(['share_percent' => 'Share cannot exceed your own share ('.$myShare.'%).']);
            }
            if ($match > $myMatch) {
                throw ValidationException::withMessages(['commission_rate' => 'Match commission cannot exceed your own rate ('.$myMatch.'%).']);
            }
            if ($session > $mySession) {
                throw ValidationException::withMessages(['session_commission_percent' => 'Session commission cannot exceed your own rate.']);
            }
            if ($casino > $myCasino) {
                throw ValidationException::withMessages(['casino_commission_percent' => 'Casino commission cannot exceed your own rate.']);
            }
            if ($matka > $myMatka) {
                throw ValidationException::withMessages(['matka_commission_percent' => 'Matka commission cannot exceed your own rate.']);
            }
        } else {
            $share = $match = $session = $casino = $matka = 0.0;
        }

        $actor->update([
            'share_percent' => $myShare,
            'commission_type' => $myType,
            'commission_rate' => $myMatch,
            'session_commission_percent' => $mySession,
            'casino_commission_percent' => $myCasino,
            'matka_commission_percent' => $myMatka,
        ]);

        $username = $this->usernameFromName($data['name']);
        $password = $data['password'];

        $child = DB::transaction(function () use ($actor, $config, $data, $coins, $share, $match, $session, $casino, $matka, $username, $password, $wallet) {
            if ($coins > 0) {
                $wallet->debit($actor, $coins, 'admin_debit', 'Opening coins for '.$data['name'], [
                    'reference_type' => 'hierarchy_transfer',
                ]);
            }

            $child = User::create([
                'name' => $data['name'],
                'username' => $username,
                'email' => $username.'@betzone.local',
                'reference' => $data['reference'],
                'password' => $password,
                'role' => $config['role'],
                'status' => 'active',
                'parent_id' => $actor->id,
                'agent_code' => $this->generateCode($config['prefix']),
                'secure_code' => User::generateSecureCode(),
                'share_percent' => $share,
                'company_share_percent' => 5,
                'commission_type' => $data['commission_type'] ?? 'bet_by_bet',
                'commission_rate' => $match,
                'session_commission_percent' => $session,
                'casino_commission_percent' => $casino,
                'matka_commission_percent' => $matka,
            ]);

            if ($coins > 0) {
                $wallet->credit($child, $coins, 'admin_credit', 'Opening coins from '.$actor->username, [
                    'reference_type' => 'hierarchy_transfer',
                ]);
            }

            return $child;
        });

        OperationLog::record(
            'ACCOUNT CREATED',
            'NEW '.$config['label'].' '.$child->name.' ('.$child->username.') UNDER '.$actor->username.' WITH '.number_format($coins, 2).' COINS',
            $actor
        );

        return redirect()->route('agent.hierarchy.index')
            ->with('success', $config['label'].' created. Login: '.$child->username.' / '.$password.' · Code: '.$child->agent_code.' · Coins: '.number_format($coins, 2));
    }

    public function edit(Request $request, User $user)
    {
        $actor = $request->user();
        abort_unless(in_array($user->id, $actor->downlineIds(), true), 403);

        $config = $this->configForRole($user->role);
        $parent = $user->parent;

        return view('agent.hierarchy.edit', compact('actor', 'user', 'config', 'parent'));
    }

    public function update(Request $request, User $user, WalletService $wallet)
    {
        $actor = $request->user();
        abort_unless(in_array($user->id, $actor->downlineIds(), true), 403);

        $config = $this->configForRole($user->role);
        $isClient = $user->role === 'client';

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'username' => ['required', 'string', 'min:4', 'max:20', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'reference' => ['required', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:5', 'max:20'],
            'coins' => ['required', 'numeric', 'min:0', 'max:1000000000'],
            'status' => ['required', 'in:active,inactive,blocked'],
            'share_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'commission_type' => [$isClient ? 'nullable' : 'required', 'in:bet_by_bet,net_loss'],
            'commission_rate' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'session_commission_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'casino_commission_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
            'matka_commission_percent' => [$isClient ? 'nullable' : 'required', 'numeric', 'min:0', 'max:100'],
        ]);

        $parent = $user->parent;
        $share = $match = $session = $casino = $matka = 0.0;

        if (! $isClient) {
            $share = round((float) $data['share_percent'], 2);
            $match = round((float) $data['commission_rate'], 2);
            $session = round((float) $data['session_commission_percent'], 2);
            $casino = round((float) $data['casino_commission_percent'], 2);
            $matka = round((float) $data['matka_commission_percent'], 2);

            if ($parent) {
                if ($share > (float) $parent->share_percent) {
                    throw ValidationException::withMessages(['share_percent' => 'Share cannot exceed parent share ('.$parent->share_percent.'%).']);
                }
                if ($match > (float) $parent->commission_rate) {
                    throw ValidationException::withMessages(['commission_rate' => 'Match commission cannot exceed parent rate ('.$parent->commission_rate.'%).']);
                }
                if ($session > (float) $parent->session_commission_percent) {
                    throw ValidationException::withMessages(['session_commission_percent' => 'Session commission cannot exceed parent rate.']);
                }
                if ($casino > (float) $parent->casino_commission_percent) {
                    throw ValidationException::withMessages(['casino_commission_percent' => 'Casino commission cannot exceed parent rate.']);
                }
                if ($matka > (float) $parent->matka_commission_percent) {
                    throw ValidationException::withMessages(['matka_commission_percent' => 'Matka commission cannot exceed parent rate.']);
                }
            }
        }

        $coins = round((float) $data['coins'], 2);
        $difference = round($coins - (float) $user->balance, 2);

        if ($difference > 0 && $difference > (float) $actor->balance) {
            throw ValidationException::withMessages(['coins' => 'You do not have enough balance to increase this account by '.number_format($difference, 2).'.']);
        }

        DB::transaction(function () use ($actor, $user, $wallet, $data, $difference, $share, $match, $session, $casino, $matka, $isClient) {
            if ($difference > 0) {
                $wallet->debit($actor, $difference, 'admin_debit', 'Balance increase for '.$user->username, ['reference_type' => 'hierarchy_transfer']);
                $wallet->credit($user, $difference, 'admin_credit', 'Balance adjusted by '.$actor->username, ['reference_type' => 'hierarchy_transfer']);
            } elseif ($difference < 0) {
                $wallet->debit($user, abs($difference), 'admin_debit', 'Balance adjusted by '.$actor->username, ['reference_type' => 'hierarchy_transfer']);
                $wallet->credit($actor, abs($difference), 'admin_credit', 'Balance returned from '.$user->username, ['reference_type' => 'hierarchy_transfer']);
            }

            $payload = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'reference' => $data['reference'],
                'status' => $data['status'],
                'share_percent' => $share,
                'commission_type' => $isClient ? 'bet_by_bet' : $data['commission_type'],
                'commission_rate' => $match,
                'session_commission_percent' => $session,
                'casino_commission_percent' => $casino,
                'matka_commission_percent' => $matka,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user->update($payload);
        });

        OperationLog::record('ACCOUNT UPDATED', 'ACCOUNT '.$user->username.' UPDATED BY '.$actor->username, $actor);

        return redirect()->route('agent.hierarchy.index')->with('success', $config['label'].' updated successfully.');
    }

    private function configForRole(string $role): array
    {
        foreach (Hierarchy::all() as $config) {
            if ($config['role'] === $role) {
                return $config;
            }
        }

        abort(404);
    }

    private function usernameFromName(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'user';
        $base = substr($base, 0, 16);
        $username = $base;

        while (User::where('username', $username)->exists()) {
            $username = $base.'_'.random_int(100, 9999);
        }

        return $username;
    }

    private function generateCode(string $prefix): string
    {
        do {
            $code = $prefix.random_int(1000, 9999);
        } while (User::where('agent_code', $code)->exists());

        return $code;
    }

    private function suggestedPassword(): string
    {
        return Str::upper(Str::random(5));
    }
}
