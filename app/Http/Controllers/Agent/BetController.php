<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\User;
use Illuminate\Http\Request;

class BetController extends Controller
{
    public function myBets(Request $request)
    {
        $user = $request->user();
        $status = $request->string('status')->toString();

        $query = $user->bets()->with('match.sport')->latest();

        if (in_array($status, ['pending', 'won', 'lost', 'void'], true)) {
            $query->where('status', $status);
        }

        $bets = $query->paginate(15)->withQueryString();

        $base = $user->bets();
        $summary = [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->where('status', 'pending')->count(),
            'won' => (clone $base)->where('status', 'won')->count(),
            'lost' => (clone $base)->where('status', 'lost')->count(),
            'staked' => (float) (clone $base)->sum('stake'),
            'returns' => (float) (clone $base)->where('status', 'won')->sum('payout'),
        ];

        return view('agent.my-bets', compact('bets', 'summary', 'status'));
    }

    public function index(Request $request)
    {
        $agent = $request->user();
        $playerIds = collect($agent->downlinePlayerIds());

        $query = Bet::with('user')->whereIn('user_id', $playerIds);

        if (in_array($request->input('status'), ['pending', 'won', 'lost', 'void'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('bet_code', 'like', "%{$search}%")
                    ->orWhere('match_label', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
            });
        }

        $summary = [
            'stake' => (float) (clone $query)->sum('stake'),
            'payout' => (float) (clone $query)->sum('payout'),
            'count' => (clone $query)->count(),
        ];

        $bets = $query->latest()->paginate(15)->withQueryString();

        return view('agent.bets.index', compact('bets', 'summary'));
    }
}
