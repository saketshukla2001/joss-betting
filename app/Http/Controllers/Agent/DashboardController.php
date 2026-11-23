<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->user();
        $playerIds = collect($agent->downlinePlayerIds());

        $bets = Bet::whereIn('user_id', $playerIds);

        $stats = [
            'players' => $playerIds->count(),
            'direct_players' => $agent->players()->count(),
            'sub_agents' => User::where('parent_id', $agent->id)->where('role', 'agent')->count(),
            'active_players' => User::whereIn('id', $playerIds)->where('status', 'active')->count(),
            'turnover' => (float) (clone $bets)->sum('stake'),
            'open_bets' => (clone $bets)->where('status', 'pending')->count(),
            'pending_commission' => (float) $agent->agentCommissions()->where('status', 'pending')->sum('amount'),
            'paid_commission' => (float) $agent->agentCommissions()->where('status', 'paid')->sum('amount'),
        ];

        $recentBets = (clone $bets)->with('user')->latest()->limit(8)->get();

        $topPlayers = (clone $bets)
            ->selectRaw('user_id, COUNT(*) as bets_count, SUM(stake) as total_stake')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total_stake')
            ->limit(5)
            ->get();

        return view('agent.dashboard', compact('agent', 'stats', 'recentBets', 'topPlayers'));
    }
}
