<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use App\Services\Cricket\CricketFeed;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $bets = $user->bets();
        $openBets = (clone $bets)->where('status', 'pending')->count();
        $wonBets = (clone $bets)->where('status', 'won')->count();
        $lostBets = (clone $bets)->where('status', 'lost')->count();
        $totalStaked = (float) (clone $bets)->sum('stake');
        $totalWon = (float) (clone $bets)->where('status', 'won')->sum('payout');
        $settled = $wonBets + $lostBets;
        $winRate = $settled > 0 ? round(($wonBets / $settled) * 100, 1) : 0;

        $recentBets = (clone $bets)->with('match.sport')->latest()->limit(5)->get();

        $marketWith = [
            'sport',
            'markets' => fn ($q) => $q->where('status', 'open')->orderBy('sort_order'),
            'markets.selections' => fn ($q) => $q->where('status', 'active')->orderBy('sort_order'),
        ];

        $staleHours = max(0, (int) setting('live_stale_hours', 6));

        $liveMatches = MatchGame::with($marketWith)->where('status', 'live')
            ->when(live_only_cricket(), fn ($query) => $query->whereHas('sport', fn ($sport) => $sport->where('slug', 'cricket')))
            ->when($staleHours > 0, fn ($query) => $query->where('start_time', '>=', now()->subHours($staleHours)))
            ->orderBy('start_time')->limit(3)->get();

        $upcomingMatches = MatchGame::with($marketWith)->where('status', 'upcoming')
            ->where('start_time', '>=', now()->subHours(2))
            ->orderBy('start_time')->limit(3)->get();

        $recentTransactions = $user->transactions()->latest()->limit(6)->get();

        $cricketFeed = [
            'matches' => [],
            'error' => null,
            'source' => null,
            'plan' => null,
        ];

        if (setting_enabled('legacy_opencricket_enabled', false)) {
            try {
                $cricketFeed = app(CricketFeed::class)->recent(4);
            } catch (\Throwable) {
                $cricketFeed = [
                    'matches' => [],
                    'error' => 'Cricket feed is unavailable.',
                    'source' => null,
                    'plan' => null,
                ];
            }
        }

        return view('dashboard', compact(
            'user', 'openBets', 'wonBets', 'lostBets', 'totalStaked', 'totalWon',
            'winRate', 'recentBets', 'liveMatches', 'upcomingMatches', 'recentTransactions',
            'cricketFeed'
        ));
    }
}
