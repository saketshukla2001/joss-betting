<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Models\Sport;
use Illuminate\Http\Request;

class SportController extends Controller
{
    public function index(Request $request, ?Sport $sport = null)
    {
        $sports = Sport::active()->get();

        $query = MatchGame::with([
            'sport',
            'markets' => fn ($q) => $q->where('status', 'open')->orderBy('sort_order'),
            'markets.selections' => fn ($q) => $q->where('status', 'active')->orderBy('sort_order'),
        ])
            ->bettingOpen();

        if ($sport) {
            $query->where('sport_id', $sport->id);
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('league', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'live') {
            $staleHours = max(0, (int) setting('live_stale_hours', 6));

            $query->where('status', 'live')
                ->when(live_only_cricket(), fn ($q) => $q->whereHas('sport', fn ($sport) => $sport->where('slug', 'cricket')))
                ->when($staleHours > 0, fn ($q) => $q->where('start_time', '>=', now()->subHours($staleHours)));
        } elseif ($request->input('status') === 'upcoming') {
            $query->where('status', 'upcoming');
        }

        $matches = $query->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderBy('start_time')
            ->paginate(9)
            ->withQueryString();

        return view('sports.index', compact('sports', 'matches', 'sport'));
    }
}
