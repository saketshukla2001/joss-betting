<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\OperationLog;
use App\Models\User;
use App\Services\WalletService;
use App\Support\Hierarchy;
use App\Support\Partnership;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class HierarchyController extends Controller
{
    public function index(Request $request, string $level)
    {
        $config = Hierarchy::get($level);
        $query = User::where('role', $config['role'])->with('parent')->withCount(['children as members_count', 'bets']);

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('agent_code', 'like', "%{$search}%")
                    ->orWhere('secure_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('code')) {
            $query->where('agent_code', 'like', '%'.trim($request->string('code')).'%');
        }

        if ($request->filled('name')) {
            $searchName = trim($request->string('name'));
            $query->where(function ($q) use ($searchName) {
                $q->where('name', 'like', "%{$searchName}%")->orWhere('username', 'like', "%{$searchName}%");
            });
        }

        if (in_array($request->input('status'), ['active', 'inactive', 'blocked'], true)) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $summary = [
            'total' => User::where('role', $config['role'])->count(),
            'active' => User::where('role', $config['role'])->where('status', 'active')->count(),
            'coins' => (float) User::where('role', $config['role'])->sum('balance'),
        ];

        $partnerships = $users->getCollection()
            ->mapWithKeys(fn (User $user) => [$user->id => Partnership::for($user)]);

        return view('admin.hierarchy.index', compact('users', 'summary', 'level', 'config', 'partnerships'));
    }

    public function create(string $level)
    {
        $config = Hierarchy::get($level);

        return view('admin.hierarchy.form', [
            'user' => new User(),
            'level' => $level,
            'config' => $config,
            'suggestedPassword' => $this->suggestedPassword(),
            'reference' => auth()->user()->reference ?: 'Mau NP',
        ]);
    }

    public function store(Request $request, string $level, WalletService $wallet)
    {
        $config = Hierarchy::get($level);
        $data = $this->validated($request, null, $config);

        $username = $this->usernameFromName($data['name']);
        $coins = round((float) ($data['coins'] ?? 0), 2);

        $user = User::create([
            'name' => $data['name'],
            'username' => $username,
            'email' => $username.'@betzone.local',
            'reference' => $data['reference'],
            'password' => $data['password'],
            'role' => $config['role'],
            'status' => 'active',
            'parent_id' => $request->user()->id,
            'agent_code' => $this->generateCode($config['prefix']),
            'secure_code' => User::generateSecureCode(),
            'share_percent' => $data['share_percent'],
            'company_share_percent' => 5,
            'commission_type' => $data['commission_type'],
            'commission_rate' => $data['commission_rate'],
            'session_commission_percent' => $data['session_commission_percent'],
            'casino_commission_percent' => $data['casino_commission_percent'],
            'matka_commission_percent' => $data['matka_commission_percent'],
        ]);

        if ($coins > 0) {
            $wallet->credit($user, $coins, 'admin_credit', 'Opening coins by '.$request->user()->username);
        }

        OperationLog::record('ACCOUNT CREATED', 'NEW '.$config['label'].' '.$user->name.' ('.$user->username.') WITH '.number_format($coins, 0).' COINS BY ['.$request->user()->agent_code.','.$request->user()->name.']', $request->user());

        return redirect()->route('admin.hierarchy.show', [$level, $user])
            ->with('success', $config['label'].' created. Login: '.$user->username.' / '.$data['password'].' · Coins: '.number_format($coins, 0));
    }

    public function show(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $user->load('parent');
        $members = $user->downlineIds();
        $transactions = $user->transactions()->latest()->limit(10)->get();
        $bets = $user->bets()->latest()->limit(10)->get();
        $stats = [
            'members' => count($members),
            'coins' => (float) $user->balance,
            'turnover' => (float) $user->bets()->sum('stake'),
            'commission' => (float) $user->agentCommissions()->sum('amount'),
        ];

        return view('admin.hierarchy.show', compact('user', 'level', 'config', 'stats', 'transactions', 'bets'));
    }

    public function edit(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        return view('admin.hierarchy.form', [
            'user' => $user,
            'level' => $level,
            'config' => $config,
            'suggestedPassword' => null,
            'reference' => $user->reference ?: auth()->user()->name,
        ]);
    }

    public function update(Request $request, string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $data = $this->validated($request, $user, $config);
        unset($data['coins']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        OperationLog::record('ACCOUNT UPDATED', 'ACCOUNT '.$user->name.' ('.$user->username.') UPDATED BY ['.$request->user()->agent_code.','.$request->user()->name.']', $request->user());

        return redirect()->route('admin.hierarchy.show', [$level, $user])
            ->with('success', $config['label'].' updated.');
    }

    public function limits(Request $request, string $level)
    {
        $config = Hierarchy::get($level);
        $users = User::where('role', $config['role'])
            ->with('parent')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.hierarchy.limits', compact('users', 'level', 'config'));
    }

    public function updateLimits(Request $request, string $level, WalletService $wallet)
    {
        $config = Hierarchy::get($level);
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
            'direction' => ['required', 'in:add,minus'],
        ]);

        $user = User::findOrFail($data['user_id']);
        abort_unless($user->role === $config['role'], 404);

        if ($data['direction'] === 'minus' && (float) $user->balance < (float) $data['amount']) {
            throw ValidationException::withMessages(['amount' => 'Amount is greater than the account chips.']);
        }

        $description = strtoupper($config['label']).' LIMIT '.($data['direction'] === 'add' ? 'ADDED' : 'MINUS').' BY '.$request->user()->username;

        if ($data['direction'] === 'add') {
            $wallet->credit($user, (float) $data['amount'], 'admin_credit', $description, [
                'reference_type' => 'limit_update', 'reference_id' => $request->user()->id,
            ]);
        } else {
            $wallet->debit($user, (float) $data['amount'], 'admin_debit', $description, [
                'reference_type' => 'limit_update', 'reference_id' => $request->user()->id,
            ]);
        }

        OperationLog::record('LIMIT UPDATED', $description.' FOR '.$user->username.' ('.$user->agent_code.')', $request->user());

        return back()->with('success', ucfirst($data['direction']).' limit of '.number_format((float) $data['amount'], 2).' coins applied to '.$user->username.'.');
    }

    public function cashForm(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $direction = request('direction') === 'withdraw' ? 'withdraw' : 'deposit';

        return view('admin.hierarchy.cash', compact('user', 'level', 'config', 'direction'));
    }

    public function cashStore(Request $request, string $level, User $user, WalletService $wallet)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $data = $request->validate([
            'direction' => ['required', 'in:deposit,withdraw'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'note' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        $description = strtoupper($data['direction']).' '.$data['note'].' · by '.$request->user()->username;

        if ($data['direction'] === 'deposit') {
            $wallet->credit($user, (float) $data['amount'], 'admin_credit', $description, [
                'reference_type' => 'hierarchy_cash', 'reference_id' => $request->user()->id,
            ]);
        } else {
            $wallet->debit($user, (float) $data['amount'], 'admin_debit', $description, [
                'reference_type' => 'hierarchy_cash', 'reference_id' => $request->user()->id,
            ]);
        }

        OperationLog::record(
            'COINS '.($data['direction'] === 'deposit' ? 'CREDITED' : 'DEBITED'),
            $description.' TO '.$user->username.' ('.$user->agent_code.')',
            $request->user()
        );

        return redirect()->route('admin.hierarchy.index', $level)
            ->with('success', ucfirst($data['direction']).' of '.number_format((float) $data['amount'], 0).' coins completed for '.$user->username.'.');
    }

    public function loginReport(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $logs = LoginLog::where('user_id', $user->id)->latest()->paginate(20);

        return view('admin.hierarchy.login-report', compact('user', 'level', 'config', 'logs'));
    }

    public function statement(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $transactions = $user->transactions()->latest()->paginate(20);

        return view('admin.hierarchy.statement', compact('user', 'level', 'config', 'transactions'));
    }

    public function toggleInactive(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $user->status = $user->status === 'inactive' ? 'active' : 'inactive';
        $user->save();

        OperationLog::record('STATUS UPDATED', 'ACCOUNT '.$user->username.' IS NOW '.strtoupper($user->status).' BY ['.request()->user()->agent_code.','.request()->user()->name.']', request()->user());

        return back()->with('success', $user->username.' is now '.$user->status.'.');
    }

    public function toggleBlock(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->save();

        OperationLog::record('BLOCK ACTIONS', 'ACCOUNT '.$user->username.' IS NOW '.strtoupper($user->status).' BY ['.request()->user()->agent_code.','.request()->user()->name.']', request()->user());

        return back()->with('success', $user->username.' is now '.$user->status.'.');
    }

    public function resetPassword(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        $password = Str::upper(Str::random(5));
        $user->update(['password' => $password]);

        OperationLog::record('PASSWORD HAS BEEN CHANGED', 'PASSWORD RESET FOR '.$user->username.' BY ['.request()->user()->agent_code.','.request()->user()->name.']', request()->user());

        return back()->with('success', 'New password for '.$user->username.' is: '.$password);
    }

    public function destroy(string $level, User $user)
    {
        $config = Hierarchy::get($level);
        abort_unless($user->role === $config['role'], 404);

        if ($user->children()->exists() || $user->bets()->exists()) {
            return back()->withErrors(['delete' => 'This account has downline members or bets and cannot be deleted.']);
        }

        $username = $user->username;
        $user->delete();

        OperationLog::record('ACCOUNT DELETED', 'ACCOUNT '.$username.' DELETED BY ['.request()->user()->agent_code.','.request()->user()->name.']', request()->user());

        return redirect()->route('admin.hierarchy.index', $level)->with('success', 'Account '.$username.' deleted.');
    }

    public function bulkDelete(Request $request, string $level)
    {
        $config = Hierarchy::get($level);
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $users = User::where('role', $config['role'])->whereIn('id', $data['ids'])->get();
        $deleted = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($user->children()->exists() || $user->bets()->exists()) {
                $skipped++;
                continue;
            }

            $user->delete();
            $deleted++;
        }

        OperationLog::record('BULK DELETE', $deleted.' '.$config['label'].' ACCOUNTS DELETED BY ['.$request->user()->agent_code.','.$request->user()->name.']', $request->user());

        return back()->with('success', $deleted.' account(s) deleted'.($skipped ? ', '.$skipped.' skipped due to downline/bets.' : '.'));
    }

    public function bulkUpdate(Request $request, string $level)
    {
        $config = Hierarchy::get($level);
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'status' => ['required', 'in:active,inactive,blocked'],
            'share_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'commission_type' => ['required', 'in:bet_by_bet,net_loss'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'session_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $updated = User::where('role', $config['role'])->whereIn('id', $data['ids'])->update([
            'status' => $data['status'],
            'share_percent' => round((float) $data['share_percent'], 2),
            'commission_type' => $data['commission_type'],
            'commission_rate' => round((float) $data['commission_rate'], 2),
            'session_commission_percent' => round((float) $data['session_commission_percent'], 2),
        ]);

        OperationLog::record('BULK UPDATE', $updated.' '.$config['label'].' ACCOUNTS UPDATED BY ['.$request->user()->agent_code.','.$request->user()->name.']', $request->user());

        return back()->with('success', $updated.' account(s) updated.');
    }

    private function validated(Request $request, ?User $user, array $config): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'reference' => ['required', 'string', 'max:120'],
            'coins' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'share_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'commission_type' => ['required', 'in:bet_by_bet,net_loss'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'session_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'casino_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'matka_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:5', 'max:20'],
        ]);

        foreach (['share_percent', 'commission_rate', 'session_commission_percent', 'casino_commission_percent', 'matka_commission_percent'] as $field) {
            $data[$field] = round((float) $data[$field], 2);
        }

        return $data;
    }

    private function usernameFromName(string $name): string
    {
        $base = Str::slug($name, '_');

        if ($base === '') {
            $base = 'user';
        }

        $base = substr($base, 0, 16);
        $username = $base;

        while (User::where('username', $username)->exists()) {
            $username = $base.'_'.random_int(100, 9999);
        }

        return $username;
    }

    private function generateCode(string $prefix): string
    {
        do {
            $code = $prefix.random_int(1000, 9999);
        } while (User::where('agent_code', $code)->exists());

        return $code;
    }

    private function suggestedPassword(): string
    {
        do {
            $password = Str::upper(Str::random(5));
        } while (strlen($password) < 5);

        return $password;
    }
}
