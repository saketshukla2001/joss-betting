<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Services\BettingService;
use Illuminate\Http\Request;

class BetController extends Controller
{
    public function store(Request $request, BettingService $betting)
    {
        $data = $request->validate([
            'selection_id' => ['required', 'integer', 'exists:selections,id'],
            'stake' => ['required', 'numeric', 'min:1', 'max:10000000'],
        ]);

        $bet = $betting->placeBet(
            $request->user(),
            (int) $data['selection_id'],
            (float) $data['stake'],
            $request->ip()
        );

        $user = $request->user()->fresh();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => ($bet->match?->provider === 'demo' && $user->exemptFromDemoWallet()
                    ? 'Demo ticket placed. Your wallet was not charged — '
                    : 'Bet placed successfully — ').$bet->bet_code,
                'bet' => [
                    'code' => $bet->bet_code,
                    'match' => $bet->match_label,
                    'selection' => $bet->selection_name,
                    'odds' => $bet->odds,
                    'stake' => $bet->stake,
                    'potential_return' => $bet->potential_return,
                ],
                'balance' => $user->balance,
                'exposure' => $user->exposure,
            ]);
        }

        $exempt = $bet->match?->provider === 'demo' && $user->exemptFromDemoWallet();

        return back()->with('success', $exempt
            ? 'Demo ticket placed. Your wallet was not charged. Ticket: '.$bet->bet_code
            : 'Bet placed successfully. Ticket: '.$bet->bet_code);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $query = $user->bets()->with('match.sport')->latest();

        if (in_array($status, ['pending', 'won', 'lost', 'void'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('bet_code', 'like', "%{$search}%")
                    ->orWhere('match_label', 'like', "%{$search}%")
                    ->orWhere('selection_name', 'like', "%{$search}%");
            });
        }

        $bets = $query->paginate(12)->withQueryString();

        $base = $user->bets();
        $summary = [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->where('status', 'pending')->count(),
            'won' => (clone $base)->where('status', 'won')->count(),
            'lost' => (clone $base)->where('status', 'lost')->count(),
            'staked' => (float) (clone $base)->sum('stake'),
            'returns' => (float) (clone $base)->where('status', 'won')->sum('payout'),
        ];

        return view('bets.index', compact('bets', 'summary', 'status', 'search'));
    }

    public function show(Request $request, Bet $bet)
    {
        abort_unless($bet->user_id === $request->user()->id, 403);

        $bet->load(['match.sport', 'market', 'selection']);

        return view('bets.show', compact('bet'));
    }
}
