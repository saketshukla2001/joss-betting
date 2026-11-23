<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\MatchGame;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public const LEVELS = [
        'my' => ['label' => 'My Ledger', 'role' => 'admin'],
        'mini_master' => ['label' => 'Mini Ledger', 'role' => 'mini_master'],
        'master_master' => ['label' => 'Master Ledger', 'role' => 'master_master'],
        'super_master' => ['label' => 'Super Ledger', 'role' => 'super_master'],
        'agent_master' => ['label' => 'Agent Ledger', 'role' => 'agent'],
        'client_master' => ['label' => 'Client Ledger', 'role' => 'client'],
    ];

    public function show(Request $request, string $level)
    {
        abort_unless(isset(self::LEVELS[$level]), 404);

        if ($level === 'my') {
            return $this->myLedger($request);
        }

        $config = self::LEVELS[$level];
        $users = User::where('role', $config['role'])->orderBy('username')->get();

        $lena = $users->filter(fn (User $user) => (float) $user->balance > 0)->sortByDesc('balance')->values();
        $dena = $users->filter(fn (User $user) => (float) $user->balance < 0)->sortBy('balance')->values();
        $clear = $users->filter(fn (User $user) => (float) $user->balance === 0.0)->values();

        $totals = [
            'lena' => round($lena->sum(fn (User $user) => (float) $user->balance), 2),
            'dena' => round(abs($dena->sum(fn (User $user) => (float) $user->balance)), 2),
            'clear' => 0.0,
        ];

        $titles = [
            'mini_master' => 'MINI MASTER LEDGER',
            'master_master' => 'MASTER MASTER LEDGER',
            'super_master' => 'SUPER MASTER LEDGER',
            'agent_master' => 'AGENT MASTER LEDGER',
            'client_master' => 'CLIENT MASTER LEDGER',
        ];

        return view('admin.classic.level-ledger', [
            'level' => $level,
            'config' => $config,
            'title' => $titles[$level],
            'lena' => $lena,
            'dena' => $dena,
            'clear' => $clear,
            'totals' => $totals,
        ]);
    }

    private function myLedger(Request $request)
    {
        $user = $request->user();
        $query = WalletTransaction::where('user_id', $user->id);
        $summaryQuery = clone $query;

        $transactions = $query->latest()->paginate(20);

        $summary = [
            'lena' => round((float) (clone $summaryQuery)->where('amount', '>', 0)->sum('amount'), 2),
            'dena' => round(abs((float) (clone $summaryQuery)->where('amount', '<', 0)->sum('amount')), 2),
            'balance' => round((float) $user->balance, 2),
        ];

        return view('admin.classic.my-ledger', compact('transactions', 'summary', 'user'));
    }

    public function profitLoss(Request $request)
    {
        $query = MatchGame::with('sport')
            ->whereIn('status', ['settled', 'cancelled'])
            ->withSum('bets as stake_sum', 'stake')
            ->withSum('bets as payout_sum', 'payout');

        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_time', '<=', $request->input('end_date'));
        }

        $matches = $query->orderByDesc('start_time')->paginate(20)->withQueryString();

        $matches->getCollection()->transform(function (MatchGame $match) {
            $pl = round((float) $match->stake_sum - (float) $match->payout_sum, 2);
            $winner = match ($match->result) {
                'home' => $match->home_team,
                'away' => $match->away_team,
                'draw' => 'DRAW',
                default => 'N/A',
            };

            $match->setAttribute('pl', $pl);
            $match->setAttribute('winner_label', $winner);
            $match->setAttribute('credit', $pl > 0 ? $pl : 0.0);
            $match->setAttribute('debit', $pl < 0 ? abs($pl) : 0.0);

            return $match;
        });

        $betQuery = Bet::whereHas('match', function ($q) use ($request) {
            $q->whereIn('status', ['settled', 'cancelled']);
            if ($request->filled('start_date')) {
                $q->whereDate('start_time', '>=', $request->input('start_date'));
            }
            if ($request->filled('end_date')) {
                $q->whereDate('start_time', '<=', $request->input('end_date'));
            }
        });

        $total = round((float) (clone $betQuery)->sum('stake') - (float) (clone $betQuery)->sum('payout'), 2);

        return view('admin.classic.match-ledger', compact('matches', 'total'));
    }
}
