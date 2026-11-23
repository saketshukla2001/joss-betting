<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Sport;
use App\Services\BettingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchGame::with('sport')->withCount(['markets', 'bets']);

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('league', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->integer('sport_id'));
        }

        if (in_array($request->input('status'), ['upcoming', 'live', 'settled', 'cancelled'], true)) {
            $query->where('status', $request->input('status'));
        }

        $matches = $query->orderByDesc('start_time')->paginate(15)->withQueryString();

        $sports = Sport::orderBy('sort_order')->get();

        $summary = [
            'upcoming' => MatchGame::where('status', 'upcoming')->count(),
            'live' => MatchGame::where('status', 'live')->count(),
            'settled' => MatchGame::where('status', 'settled')->count(),
            'cancelled' => MatchGame::where('status', 'cancelled')->count(),
        ];

        return view('admin.matches.index', compact('matches', 'sports', 'summary'));
    }

    public function create()
    {
        $sports = Sport::orderBy('sort_order')->get();

        return view('admin.matches.form', ['match' => new MatchGame(), 'sports' => $sports]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sport_id' => ['required', 'exists:sports,id'],
            'league' => ['required', 'string', 'max:120'],
            'home_team' => ['required', 'string', 'max:120'],
            'away_team' => ['required', 'string', 'max:120', 'different:home_team'],
            'start_time' => ['required', 'date'],
            'status' => ['required', 'in:upcoming,live'],
            'home_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'away_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $data['featured'] = $request->boolean('featured');

        $match = MatchGame::create($data);

        if ($request->boolean('create_default_market', true)) {
            $this->createDefaultMarket($match);
        }

        return redirect()->route('admin.matches.show', $match)
            ->with('success', 'Match created. Add or edit markets and odds below.');
    }

    public function show(MatchGame $match)
    {
        $match->load([
            'sport',
            'markets' => fn ($query) => $query->withCount([
                'bets as pending_bets_count' => fn ($q) => $q->where('status', 'pending'),
                'bets as all_bets_count',
            ])->orderBy('sort_order'),
            'markets.selections' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $bets = $match->bets()->with('user')->latest()->limit(10)->get();

        $betStats = [
            'count' => $match->bets()->count(),
            'pending' => $match->bets()->where('status', 'pending')->count(),
            'stake' => (float) $match->bets()->sum('stake'),
            'payout' => (float) $match->bets()->sum('payout'),
        ];

        return view('admin.matches.show', compact('match', 'bets', 'betStats'));
    }

    public function edit(MatchGame $match)
    {
        $sports = Sport::orderBy('sort_order')->get();

        return view('admin.matches.form', compact('match', 'sports'));
    }

    public function update(Request $request, MatchGame $match)
    {
        $data = $request->validate([
            'sport_id' => ['required', 'exists:sports,id'],
            'league' => ['required', 'string', 'max:120'],
            'home_team' => ['required', 'string', 'max:120'],
            'away_team' => ['required', 'string', 'max:120', 'different:home_team'],
            'start_time' => ['required', 'date'],
            'status' => ['required', 'in:upcoming,live,settled,cancelled'],
            'home_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'away_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $data['featured'] = $request->boolean('featured');

        if ($data['status'] === 'cancelled' && $match->status !== 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Use the "Void & Refund" action to cancel a match so all open bets are refunded.',
            ]);
        }

        if ($data['status'] === 'settled' && $match->markets()->whereIn('status', ['open', 'suspended'])->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Settle or void every market before marking the match settled.',
            ]);
        }

        $match->update($data);

        return redirect()->route('admin.matches.show', $match)->with('success', 'Match updated successfully.');
    }

    public function destroy(MatchGame $match)
    {
        if ($match->bets()->exists()) {
            return back()->withErrors(['match' => 'This match has bets and cannot be deleted. Void it instead.']);
        }

        $match->delete();

        return redirect()->route('admin.matches.index')->with('success', 'Match deleted.');
    }

    public function declareResult(Request $request, MatchGame $match, BettingService $betting)
    {
        $data = $request->validate([
            'result' => ['required', 'in:home,away,draw'],
            'home_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'away_score' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        if ($match->status === 'cancelled') {
            throw ValidationException::withMessages(['result' => 'This match has been cancelled.']);
        }

        $match->update([
            'home_score' => $data['home_score'] ?? $match->home_score,
            'away_score' => $data['away_score'] ?? $match->away_score,
        ]);

        $count = $betting->declareResult($match, $data['result']);

        return back()->with('success', 'Result saved. '.$count.' market(s) auto-settled.');
    }

    public function voidMatch(MatchGame $match, BettingService $betting)
    {
        $count = $betting->voidMatch($match);

        return back()->with('success', 'Match voided. '.$count.' market(s) refunded.');
    }

    private function createDefaultMarket(MatchGame $match): void
    {
        $market = $match->markets()->create([
            'name' => 'Match Winner',
            'auto_settle' => true,
            'status' => 'open',
            'sort_order' => 1,
        ]);

        $selections = [
            ['name' => $match->home_team, 'result_key' => 'home', 'odds' => 1.90, 'status' => 'active', 'sort_order' => 1],
            ['name' => $match->away_team, 'result_key' => 'away', 'odds' => 2.10, 'status' => 'active', 'sort_order' => 2],
        ];

        if ($match->sport?->slug === 'football') {
            $selections[] = ['name' => 'Draw', 'result_key' => 'draw', 'odds' => 3.30, 'status' => 'active', 'sort_order' => 3];
        }

        $market->selections()->createMany($selections);
    }
}
