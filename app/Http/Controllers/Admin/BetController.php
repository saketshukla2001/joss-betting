<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\Sport;
use App\Services\BettingService;
use Illuminate\Http\Request;

class BetController extends Controller
{
    public function index(Request $request)
    {
        $query = Bet::with(['user', 'match.sport']);

        if (in_array($request->input('status'), ['pending', 'won', 'lost', 'void'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('sport_id')) {
            $sport = Sport::find($request->integer('sport_id'));
            if ($sport) {
                $query->where('sport_name', $sport->name);
            }
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('bet_code', 'like', "%{$search}%")
                    ->orWhere('match_label', 'like', "%{$search}%")
                    ->orWhere('selection_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $summary = [
            'count' => (clone $query)->count(),
            'stake' => (float) (clone $query)->sum('stake'),
            'payout' => (float) (clone $query)->sum('payout'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];

        $bets = $query->latest()->paginate(15)->withQueryString();
        $sports = Sport::orderBy('sort_order')->get();

        return view('admin.bets.index', compact('bets', 'summary', 'sports'));
    }

    public function show(Bet $bet)
    {
        $bet->load(['user', 'match.sport', 'market', 'selection']);

        $marketOptions = $bet->match
            ? $bet->match->markets()
                ->with(['selections' => fn ($query) => $query->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
            : collect();

        $transactions = $bet->user->transactions()
            ->where('reference_type', 'bet')
            ->where('reference_id', $bet->id)
            ->latest()
            ->get();

        return view('admin.bets.show', compact('bet', 'transactions', 'marketOptions'));
    }

    public function update(Request $request, Bet $bet, BettingService $betting)
    {
        $data = $request->validate([
            'stake' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'odds' => ['required', 'numeric', 'min:1.01', 'max:1000'],
            'selection_id' => ['nullable', 'integer', 'exists:selections,id'],
        ]);

        $betting->adjustBet(
            $bet,
            (float) $data['stake'],
            (float) $data['odds'],
            isset($data['selection_id']) ? (int) $data['selection_id'] : null,
            $request->user()
        );

        return redirect()->route('admin.bets.show', $bet)
            ->with('success', 'Ticket '.$bet->bet_code.' updated everywhere — match screens, reports and the player ledger now show the corrected ticket.');
    }
}
