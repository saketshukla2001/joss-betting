<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;

/**
 * Client view of simulated cricket. The rows stay out of the real sportsbook list.
 */
class DemoCricketController extends Controller
{
    public function index()
    {
        return redirect()->route('cricket.index');
    }

    public function show(int $match)
    {
        $match = MatchGame::withoutGlobalScope('real_money')
            ->where('provider', 'demo')
            ->findOrFail($match);

        return redirect()->route('matches.show', $match);
    }
}
