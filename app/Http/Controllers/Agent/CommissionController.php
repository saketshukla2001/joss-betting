<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->user();

        $query = AgentCommission::with(['player', 'bet'])->where('agent_id', $agent->id);

        if (in_array($request->input('status'), ['pending', 'paid'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('player', fn ($p) => $p->where('username', 'like', "%{$search}%"))
                    ->orWhereHas('bet', fn ($b) => $b->where('bet_code', 'like', "%{$search}%"));
            });
        }

        $summary = [
            'pending' => (float) $agent->agentCommissions()->where('status', 'pending')->sum('amount'),
            'paid' => (float) $agent->agentCommissions()->where('status', 'paid')->sum('amount'),
            'entries' => $agent->agentCommissions()->count(),
        ];

        $entries = $query->latest()->paginate(20)->withQueryString();

        return view('agent.commissions.index', compact('agent', 'entries', 'summary'));
    }
}
