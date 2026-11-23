<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::agents()
            ->with('parent')
            ->withCount('players')
            ->withSum(['agentCommissions as pending_commission' => fn ($q) => $q->where('status', 'pending')], 'amount')
            ->withSum(['agentCommissions as paid_commission' => fn ($q) => $q->where('status', 'paid')], 'amount');

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('agent_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($request->input('status'), ['active', 'blocked'], true)) {
            $query->where('status', $request->input('status'));
        }

        $agents = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => User::agents()->count(),
            'active' => User::agents()->where('status', 'active')->count(),
            'pending' => (float) \App\Models\AgentCommission::where('status', 'pending')->sum('amount'),
            'paid' => (float) \App\Models\AgentCommission::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.agents.index', compact('agents', 'summary'));
    }

    public function create()
    {
        $parents = User::agents()->where('status', 'active')->orderBy('name')->get();

        return view('admin.agents.form', ['agent' => new User(), 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['role'] = 'agent';
        $data['parent_id'] = $this->validatedParent($request, null);
        $data['agent_code'] = $this->generateCode();
        $data['password'] = $data['password'];

        $agent = User::create($data);

        return redirect()->route('admin.agents.show', $agent)
            ->with('success', 'Agent created. Share the login credentials with them.');
    }

    public function show(User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $players = $agent->players()->withCount('bets')->orderByDesc('created_at')->get();
        $playerIds = $players->pluck('id');
        $subAgents = User::where('parent_id', $agent->id)->where('role', 'agent')->withCount('players')->get();

        $stats = [
            'players' => $players->count(),
            'downline_players' => count($agent->downlinePlayerIds()),
            'sub_agents' => $subAgents->count(),
            'active_players' => $players->where('status', 'active')->count(),
            'turnover' => (float) Bet::whereIn('user_id', $playerIds)->sum('stake'),
            'open_bets' => Bet::whereIn('user_id', $playerIds)->where('status', 'pending')->count(),
            'pending' => $agent->agentCommissions()->where('status', 'pending')->sum('amount'),
            'paid' => $agent->agentCommissions()->where('status', 'paid')->sum('amount'),
        ];

        $bets = Bet::whereIn('user_id', $playerIds)->with('user')->latest()->limit(10)->get();
        $commissions = $agent->agentCommissions()->with(['player', 'bet'])->latest()->limit(10)->get();

        return view('admin.agents.show', compact('agent', 'players', 'subAgents', 'stats', 'bets', 'commissions'));
    }

    public function edit(User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $parents = User::agents()
            ->where('id', '!=', $agent->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->reject(fn (User $candidate) => $this->isDescendant($agent, $candidate->id));

        return view('admin.agents.form', compact('agent', 'parents'));
    }

    public function update(Request $request, User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $data = $this->validated($request, $agent);
        $data['parent_id'] = $this->validatedParent($request, $agent);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $agent->update($data);

        return redirect()->route('admin.agents.show', $agent)->with('success', 'Agent updated successfully.');
    }

    public function payout(User $agent, CommissionService $commissions)
    {
        abort_unless($agent->isAgent(), 404);

        $result = $commissions->payout($agent, request()->user());

        return back()->with('success', 'Commission payout of '.money($result['total'])
            .' completed for '.$agent->username.' ('.$result['count'].' entries, ref '.$result['reference'].').');
    }

    private function validated(Request $request, ?User $agent = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'username' => ['required', 'string', 'min:4', 'max:20', 'alpha_dash', Rule::unique('users', 'username')->ignore($agent?->id)],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($agent?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,blocked'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:50'],
            'commission_type' => ['required', 'in:turnover,net_loss'],
            'password' => [$agent ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $data['commission_rate'] = round((float) $data['commission_rate'], 2);

        return $data;
    }

    private function validatedParent(Request $request, ?User $agent): ?int
    {
        $parentId = $request->input('parent_id') ? (int) $request->input('parent_id') : null;

        if (! $parentId) {
            return null;
        }

        $parent = User::find($parentId);

        if (! $parent || ! $parent->isAgent()) {
            throw ValidationException::withMessages(['parent_id' => 'Choose a valid parent agent.']);
        }

        if ($agent && ($parentId === $agent->id || $this->isDescendant($agent, $parentId))) {
            throw ValidationException::withMessages(['parent_id' => 'An agent cannot be placed under themselves or their downline.']);
        }

        return $parentId;
    }

    private function isDescendant(User $agent, int $candidateId): bool
    {
        $current = $candidateId;
        $guard = 0;

        while ($current && $guard < 15) {
            if ($current === $agent->id) {
                return true;
            }

            $current = (int) DB::table('users')->where('id', $current)->value('parent_id');
            $guard++;
        }

        return false;
    }

    private function generateCode(): string
    {
        do {
            $code = 'AG'.random_int(1000, 9999);
        } while (User::where('agent_code', $code)->exists());

        return $code;
    }
}
