<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentCommission::with(['agent', 'player', 'bet']);

        if (in_array($request->input('status'), ['pending', 'paid'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->integer('agent_id'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('agent', fn ($a) => $a->where('username', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"))
                    ->orWhereHas('player', fn ($p) => $p->where('username', 'like', "%{$search}%"));
            });
        }

        $summary = [
            'pending' => (float) AgentCommission::where('status', 'pending')->sum('amount'),
            'paid' => (float) AgentCommission::where('status', 'paid')->sum('amount'),
            'entries' => AgentCommission::count(),
        ];

        $entries = $query->latest()->paginate(20)->withQueryString();

        $agents = User::agents()
            ->withSum(['agentCommissions as pending_commission' => fn ($q) => $q->where('status', 'pending')], 'amount')
            ->orderByDesc('pending_commission')
            ->get()
            ->filter(fn (User $agent) => (float) $agent->pending_commission > 0);

        return view('admin.commissions.index', compact('entries', 'summary', 'agents'));
    }

    public function payout(Request $request, User $agent, CommissionService $commissions)
    {
        abort_unless($agent->isAgent(), 404);

        $result = $commissions->payout($agent, $request->user());

        return back()->with('success', 'Paid '.money($result['total']).' to '.$agent->username
            .' across '.$result['count'].' commission entries.');
    }
}
