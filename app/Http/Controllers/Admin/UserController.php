<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('parent')->withCount('bets');

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (in_array($request->input('role'), ['admin', 'agent', 'client'], true)) {
            $query->where('role', $request->input('role'));
        }

        if (in_array($request->input('status'), ['active', 'blocked'], true)) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => User::clients()->count(),
            'active' => User::clients()->where('status', 'active')->count(),
            'blocked' => User::clients()->where('status', 'blocked')->count(),
            'balance' => (float) User::clients()->sum('balance'),
            'exposure' => (float) User::clients()->sum('exposure'),
        ];

        return view('admin.users.index', compact('users', 'summary'));
    }

    public function create()
    {
        $agents = User::agents()->where('status', 'active')->orderBy('name')->get();

        return view('admin.users.form', ['user' => new User(), 'agents' => $agents]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'username' => ['required', 'string', 'min:4', 'max:20', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:admin,client'],
            'status' => ['required', 'in:active,blocked'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'starting_balance' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'parent_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'status' => $data['status'],
                'password' => $data['password'],
                'parent_id' => $data['role'] === 'client' ? ($data['parent_id'] ?? null) : null,
            ]);

            $balance = (float) ($data['starting_balance'] ?? 0);

            if ($balance > 0) {
                app(WalletService::class)->credit(
                    $user,
                    $balance,
                    'admin_credit',
                    'Opening balance credited by admin'
                );
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->loadCount('bets');

        $bets = $user->bets()->with('match.sport')->latest()->paginate(8, ['*'], 'bets_page');
        $transactions = $user->transactions()->latest()->paginate(10, ['*'], 'tx_page');

        $stats = [
            'staked' => (float) $user->bets()->sum('stake'),
            'returns' => (float) $user->bets()->where('status', 'won')->sum('payout'),
            'open' => $user->bets()->where('status', 'pending')->count(),
            'deposits' => (float) $user->depositRequests()->where('status', 'approved')->sum('amount'),
            'withdrawals' => (float) $user->withdrawalRequests()->where('status', 'approved')->sum('amount'),
        ];

        return view('admin.users.show', compact('user', 'bets', 'transactions', 'stats'));
    }

    public function edit(User $user)
    {
        $agents = User::agents()->where('status', 'active')->orderBy('name')->get();

        return view('admin.users.form', compact('user', 'agents'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:admin,client'],
            'status' => ['required', 'in:active,blocked'],
            'parent_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $data['parent_id'] = $data['role'] === 'client' ? ($data['parent_id'] ?? null) : null;

        if ($user->id === Auth::id() && $data['role'] !== 'admin') {
            throw ValidationException::withMessages(['role' => 'You cannot remove your own administrator role.']);
        }

        if ($user->id === Auth::id() && $data['status'] !== 'active') {
            throw ValidationException::withMessages(['status' => 'You cannot block your own account.']);
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.');
    }

    public function adjustBalance(Request $request, User $user, WalletService $wallet)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'direction' => ['required', 'in:credit,debit'],
            'note' => ['required', 'string', 'min:3', 'max:200'],
        ]);

        $amount = round((float) $data['amount'], 2);
        $description = $data['note'].' · adjusted by '.Auth::user()->username;

        if ($data['direction'] === 'credit') {
            $wallet->credit($user, $amount, 'admin_credit', $description, ['reference_type' => 'admin', 'reference_id' => Auth::id()]);
            $message = 'Wallet credited with '.money($amount).'.';
        } else {
            $wallet->debit($user, $amount, 'admin_debit', $description, ['reference_type' => 'admin', 'reference_id' => Auth::id()]);
            $message = 'Wallet debited with '.money($amount).'.';
        }

        return back()->with('success', $message);
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->update(['password' => $data['password']]);

        return back()->with('success', 'Password reset for '.$user->username.'.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            throw ValidationException::withMessages(['status' => 'You cannot block your own account.']);
        }

        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();

        return back()->with('success', 'User '.$user->username.' is now '.$user->status.'.');
    }
}
