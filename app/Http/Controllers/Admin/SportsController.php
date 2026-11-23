<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\MatchLimit;
use App\Models\MatchGame;
use App\Models\Sport;
use App\Models\User;
use App\Models\OperationLog;
use Illuminate\Http\Request;

class SportsController extends Controller
{
    public function inplay(Request $request)
    {
        $query = MatchGame::with('sport')->whereIn('status', ['live', 'upcoming']);

        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_time', '<=', $request->input('end_date'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('league', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort') === 'desc' ? 'desc' : 'asc';
        $matches = $query->orderBy('start_time', $sort)->paginate(10)->withQueryString();

        return view('admin.classic.sports', [
            'matches' => $matches,
            'mode' => 'inplay',
            'sports' => Sport::orderBy('sort_order')->get(),
            'sort' => $sort,
        ]);
    }

    public function matchView(Request $request, MatchGame $match)
    {
        $match->load(['sport', 'markets.selections', 'bets.user']);
        $bets = $match->bets->sortByDesc('created_at')->values();

        $winnerMarket = $match->markets->firstWhere('name', 'Match Winner');
        $homeSelection = $winnerMarket?->selections->firstWhere('result_key', 'home') ?? $winnerMarket?->selections->first();
        $awaySelection = $winnerMarket?->selections->firstWhere('result_key', 'away') ?? $winnerMarket?->selections->last();

        $teamRows = collect([
            ['team' => $match->home_team, 'selection' => $homeSelection],
            ['team' => $match->away_team, 'selection' => $awaySelection],
        ])->map(function ($row) {
            $lagai = $row['selection'] ? (int) round((float) $row['selection']->odds * 30) : 0;

            return [
                'team' => $row['team'],
                'lagai' => $lagai,
                'khai' => $lagai > 0 ? $lagai + 3 : 0,
            ];
        });

        $fancyMarkets = $match->markets->reject(fn ($market) => $market->name === 'Match Winner')->values();
        $fancyRows = $fancyMarkets->map(function ($market, $index) {
            $first = $market->selections->first();
            $second = $market->selections->last();
            $not = $first ? (int) round((float) $first->odds * 30) : 0;
            $yes = $second ? (int) round((float) $second->odds * 30) : $not;
            $suspended = $market->status !== 'open' || $market->selections->contains(fn ($selection) => $selection->status !== 'active');

            return [
                'name' => $market->name,
                'limit' => [50000, 25000, 10000][$index % 3],
                'badge' => $index % 3 === 0,
                'not' => $not,
                'yes' => $yes,
                'suspended' => $suspended,
            ];
        });

        $sessionBets = $bets->filter(fn ($bet) => $bet->market_name !== 'Match Winner');
        $clientSessionRows = $sessionBets->groupBy('user_id')->map(function ($userBets) use ($request) {
            $user = $userBets->first()->user;
            $stake = round((float) $userBets->sum('stake'), 2);
            $comm = round($stake * 0.15, 2);
            $totalAmount = round($stake - $comm, 2);
            $myShare = round($totalAmount * (((float) ($request->user()->share_percent ?? 95)) / 100), 2);

            return [
                'user' => $user,
                'total' => $stake,
                'session_amt' => $stake,
                'session_comm' => $comm,
                'total_comm' => $comm,
                'total_amount' => $totalAmount,
                'my_share' => $myShare,
                'net_pl' => round($totalAmount - $myShare, 2),
            ];
        })->values();

        $clientTotals = [
            'total' => round($clientSessionRows->sum('total'), 2),
            'session_amt' => round($clientSessionRows->sum('session_amt'), 2),
            'session_comm' => round($clientSessionRows->sum('session_comm'), 2),
            'total_comm' => round($clientSessionRows->sum('total_comm'), 2),
            'total_amount' => round($clientSessionRows->sum('total_amount'), 2),
            'my_share' => round($clientSessionRows->sum('my_share'), 2),
            'net_pl' => round($clientSessionRows->sum('net_pl'), 2),
        ];

        $completedSessions = $fancyMarkets->map(function ($market, $index) use ($bets) {
            $marketBets = $bets->where('market_id', $market->id);
            $pl = $marketBets->sum(function ($bet) {
                if ($bet->status === 'won') return (float) $bet->payout - (float) $bet->stake;
                if ($bet->status === 'lost') return -1 * (float) $bet->stake;
                return 0;
            });
            $winner = $market->selections->firstWhere('result', 'win');

            return [
                'no' => $index + 1,
                'name' => $market->name,
                'winner' => $winner?->result_key ?? '—',
                'comm_plus' => 0,
                'comm_minus' => 0,
                'pl' => round((float) $pl, 2),
                'market_id' => $market->id,
            ];
        });

        return view('admin.classic.match-view', compact(
            'match', 'teamRows', 'fancyRows', 'clientSessionRows', 'clientTotals', 'completedSessions', 'bets'
        ));
    }

    public function matchLimits(MatchGame $match)
    {
        $masters = User::where('role', 'master_master')
            ->with('parent')
            ->orderByDesc('created_at')
            ->paginate(10);

        $limits = MatchLimit::where('match_id', $match->id)
            ->whereIn('user_id', $masters->pluck('id'))
            ->pluck('amount', 'user_id');

        return view('admin.classic.match-limits', compact('match', 'masters', 'limits'));
    }

    public function updateMatchLimit(Request $request, MatchGame $match)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'direction' => ['required', 'in:add,minus'],
        ]);

        $user = User::findOrFail($data['user_id']);
        abort_unless($user->role === 'master_master', 404);

        $limit = MatchLimit::firstOrCreate(
            ['match_id' => $match->id, 'user_id' => $user->id],
            ['amount' => 0]
        );

        $limit->amount = $data['direction'] === 'add'
            ? $limit->amount + (float) $data['amount']
            : max(0, $limit->amount - (float) $data['amount']);
        $limit->save();

        OperationLog::record(
            'MATCH LIMIT UPDATED',
            strtoupper($match->label).' LIMIT '.($data['direction'] === 'add' ? 'ADDED' : 'MINUS').' FOR '.$user->username.' BY ['.$request->user()->agent_code.','.$request->user()->name.']',
            $request->user()
        );

        return back()->with('success', 'Match limit for '.$user->username.' updated.');
    }

    public function matchBets(Request $request, MatchGame $match, string $type)
    {
        abort_unless(in_array($type, ['match', 'completed-match', 'session', 'session-all', 'completed-session', 'rejected'], true), 404);

        $query = Bet::with('user')->where('match_id', $match->id);

        if ($type === 'match') {
            $query->where('status', 'pending');
        } elseif ($type === 'completed-match') {
            $query->where('status', '!=', 'pending');
        } elseif ($type === 'session') {
            $query->where('market_name', 'like', '%session%')->where('status', 'pending');
        } elseif ($type === 'session-all') {
            $query->where('market_name', 'like', '%session%');
        } elseif ($type === 'completed-session') {
            $query->where('market_name', 'like', '%session%')->where('status', '!=', 'pending');
        } else {
            $query->where('status', 'void');
        }

        $bets = $query->latest()->paginate(20)->withQueryString();

        $titles = [
            'match' => 'MATCH BET',
            'completed-match' => 'MATCH BET',
            'session' => 'SESSION BET',
            'session-all' => 'SESSION BET',
            'completed-session' => 'COMPLETED SESSION',
            'rejected' => 'REJECTED BETS',
        ];

        return view('admin.classic.match-bets', [
            'match' => $match,
            'bets' => $bets,
            'type' => $type,
            'title' => $titles[$type],
        ]);
    }

    public function completed(Request $request)
    {
        $query = MatchGame::with('sport')->whereIn('status', ['settled', 'cancelled']);

        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_time', '<=', $request->input('end_date'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('league', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        $matches = $query->orderBy('start_time', $sort)->paginate(10)->withQueryString();

        return view('admin.classic.sports', [
            'matches' => $matches,
            'mode' => 'completed',
            'sports' => Sport::orderBy('sort_order')->get(),
            'sort' => $sort,
        ]);
    }

    public function matchPlReport(MatchGame $match)
    {
        $bets = Bet::with('user')->where('match_id', $match->id)->latest()->get();
        $sessionBets = $bets->filter(fn (Bet $bet) => str_contains(strtolower((string) $bet->market_name), 'session'))->values();
        $matchBets = $bets->reject(fn (Bet $bet) => str_contains(strtolower((string) $bet->market_name), 'session'))->values();

        $totals = [
            'match' => round($matchBets->sum(fn (Bet $bet) => $this->betPl($bet)), 2),
            'session' => round($sessionBets->sum(fn (Bet $bet) => $this->betPl($bet)), 2),
        ];
        $totals['total'] = round($totals['match'] + $totals['session'], 2);

        return view('admin.classic.match-pl-report', compact('match', 'matchBets', 'sessionBets', 'totals'));
    }

    public function matchPl(MatchGame $match)
    {
        $bets = Bet::where('match_id', $match->id)->get();
        $rows = [];

        foreach ($bets as $bet) {
            $isSession = str_contains(strtolower((string) $bet->market_name), 'session');
            $key = $bet->market_name;

            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'market' => $bet->market_name,
                    'type' => $isSession ? 'SESSION' : 'MATCH',
                    'bets' => 0,
                    'stake' => 0.0,
                    'payout' => 0.0,
                    'pl' => 0.0,
                ];
            }

            $rows[$key]['bets']++;
            $rows[$key]['stake'] += (float) $bet->stake;
            $rows[$key]['payout'] += (float) $bet->payout;
            $rows[$key]['pl'] += $this->betPl($bet);
        }

        foreach ($rows as &$row) {
            $row['stake'] = round($row['stake'], 2);
            $row['payout'] = round($row['payout'], 2);
            $row['pl'] = round($row['pl'], 2);
        }
        unset($row);

        $totals = [
            'match' => round(collect($rows)->where('type', 'MATCH')->sum('pl'), 2),
            'session' => round(collect($rows)->where('type', 'SESSION')->sum('pl'), 2),
        ];
        $totals['total'] = round($totals['match'] + $totals['session'], 2);

        return view('admin.classic.match-pl', compact('match', 'rows', 'totals'));
    }

    private function betPl(Bet $bet): float
    {
        return match ($bet->status) {
            'won' => round((float) $bet->payout - (float) $bet->stake, 2),
            'lost' => round(-1 * (float) $bet->stake, 2),
            default => 0.0,
        };
    }

    public function currentBets(Request $request)
    {
        $query = Bet::with(['user', 'match.sport'])->where('status', 'pending');

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('bet_code', 'like', "%{$search}%")
                    ->orWhere('match_label', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
            });
        }

        $bets = $query->latest()->paginate(20)->withQueryString();
        $summary = [
            'stake' => (float) Bet::where('status', 'pending')->sum('stake'),
            'count' => Bet::where('status', 'pending')->count(),
        ];

        return view('admin.classic.current-bets', compact('bets', 'summary'));
    }

    public function toggleInplay(MatchGame $match)
    {
        $match->status = $match->status === 'live' ? 'upcoming' : 'live';
        $match->save();

        return back()->with('success', $match->label.' is now '.$match->status.'.');
    }
}
