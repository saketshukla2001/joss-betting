<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Setting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        $activeMatches = MatchGame::with('sport')
            ->whereIn('status', ['live', 'upcoming'])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderBy('start_time')
            ->limit(12)
            ->get();

        $members = $admin->children()->count();
        $rules = Setting::get('rules', 'Bet responsibly. 18+ only. All bets are subject to market settlement and platform rules.');

        return view('admin.dashboard', compact('admin', 'activeMatches', 'members', 'rules'));
    }
}
