<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->user();

        $playerIds = collect($agent->downlinePlayerIds());

        $query = User::whereIn('id', $playerIds)
            ->where('role', 'client')
            ->with('parent')
            ->withCount('bets');

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (in_array($request->input('status'), ['active', 'blocked'], true)) {
            $query->where('status', $request->input('status'));
        }

        $players = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => $playerIds->count(),
            'active' => User::whereIn('id', $playerIds)->where('role', 'client')->where('status', 'active')->count(),
            'balance' => (float) User::whereIn('id', $playerIds)->where('role', 'client')->sum('balance'),
            'downline' => count($agent->downlineIds()),
        ];

        return view('agent.players.index', compact('players', 'summary', 'agent'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->role === 'agent', 403, 'Only Agent Master can create Client Master accounts.');

        return view('agent.players.form');
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'agent', 403, 'Only Agent Master can create Client Master accounts.');

        $agent = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'username' => ['required', 'string', 'min:4', 'max:20', 'alpha_dash', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $player = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => 'client',
            'status' => 'active',
            'parent_id' => $agent->id,
        ]);

        return redirect()->route('agent.players.show', $player)->with('success', 'Player created under your agency.');
    }

    public function show(Request $request, User $player)
    {
        $agent = $request->user();

        abort_unless($player->role === 'client' && in_array($player->id, $agent->downlinePlayerIds(), true), 403);

        $stats = [
            'turnover' => (float) $player->bets()->sum('stake'),
            'bets' => $player->bets()->count(),
            'open' => $player->bets()->where('status', 'pending')->count(),
            'returns' => (float) $player->bets()->where('status', 'won')->sum('payout'),
            'commission' => (float) $agent->agentCommissions()->where('player_id', $player->id)->sum('amount'),
        ];

        $bets = $player->bets()->latest()->limit(10)->get();
        $transactions = $player->transactions()->latest()->limit(10)->get();

        return view('agent.players.show', compact('player', 'stats', 'bets', 'transactions'));
    }
}
