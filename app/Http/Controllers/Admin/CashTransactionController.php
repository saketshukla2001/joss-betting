<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Support\Hierarchy;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CashTransactionController extends Controller
{
    public const LEVELS = [
        'mini_master' => 'mini_master',
        'master_master' => 'master_master',
        'super_master' => 'super_master',
        'agent_master' => 'agent',
        'client_master' => 'client',
    ];

    public function index(Request $request, string $level)
    {
        abort_unless(isset(self::LEVELS[$level]), 404);

        $role = self::LEVELS[$level];
        $config = Hierarchy::get($level);
        $levelUsers = User::where('role', $role)->get();

        if ($role === 'client') {
            $clientIds = $levelUsers->pluck('id');
        } else {
            $clientIds = collect();
            foreach ($levelUsers as $levelUser) {
                $clientIds = $clientIds->merge($levelUser->downlinePlayerIds());
            }
        }

        $clients = User::whereIn('id', $clientIds->unique())
            ->where('role', 'client')
            ->with('parent')
            ->orderBy('username')
            ->get();

        return view('admin.classic.cash', compact('clients', 'level', 'config'));
    }

    public function store(Request $request, string $level, WalletService $wallet)
    {
        abort_unless(isset(self::LEVELS[$level]), 404);

        $role = self::LEVELS[$level];
        $config = Hierarchy::get($level);
        $levelUsers = User::where('role', $role)->get();

        if ($role === 'client') {
            $allowedIds = $levelUsers->pluck('id');
        } else {
            $allowedIds = collect();
            foreach ($levelUsers as $levelUser) {
                $allowedIds = $allowedIds->merge($levelUser->downlinePlayerIds());
            }
        }

        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:users,id'],
            'collection' => ['required', 'string', 'max:80'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'payment_type' => ['required', 'in:CASH,ONLINE,BANK,UPI'],
            'comment' => ['required', 'string', 'min:2', 'max:200'],
        ]);

        $client = User::findOrFail($data['client_id']);

        if ($client->role !== 'client' || ! $allowedIds->contains($client->id)) {
            throw ValidationException::withMessages(['client_id' => 'This client does not belong to the selected level.']);
        }

        $description = 'CASH COLLECTION '.$data['collection'].' VIA '.$data['payment_type'].' · '.$data['comment'];

        $wallet->credit($client, (float) $data['amount'], 'admin_credit', $description, [
            'reference_type' => 'cash_transaction',
            'reference_id' => $request->user()->id,
            'meta' => ['date' => $data['date'], 'collection' => $data['collection'], 'payment_type' => $data['payment_type']],
        ]);

        OperationLog::record(
            'CASH TRANSACTION',
            $description.' TO '.$client->username.' ('.$client->agent_code.') BY ['.$request->user()->agent_code.','.$request->user()->name.']',
            $request->user()
        );

        return back()->with('success', 'Cash transaction of '.number_format((float) $data['amount'], 0).' coins completed for '.$client->username.'.');
    }

    public function adjust(Request $request, string $level, WalletService $wallet)
    {
        abort_unless(isset(self::LEVELS[$level]), 404);

        $role = self::LEVELS[$level];
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'direction' => ['required', 'in:credit,debit'],
            'note' => ['required', 'string', 'min:3', 'max:200'],
        ]);

        $user = User::findOrFail($data['user_id']);

        if ($user->role !== $role) {
            throw ValidationException::withMessages(['user_id' => 'This account does not belong to the selected level.']);
        }

        $description = $data['note'].' · by '.$request->user()->username;

        if ($data['direction'] === 'credit') {
            $wallet->credit($user, (float) $data['amount'], 'admin_credit', $description, [
                'reference_type' => 'cash', 'reference_id' => $request->user()->id,
            ]);
            $message = 'Credited '.money($data['amount']).' to '.$user->username.'.';
        } else {
            $wallet->debit($user, (float) $data['amount'], 'admin_debit', $description, [
                'reference_type' => 'cash', 'reference_id' => $request->user()->id,
            ]);
            $message = 'Debited '.money($data['amount']).' from '.$user->username.'.';
        }

        OperationLog::record(
            $data['direction'] === 'credit' ? 'COINS CREDITED' : 'COINS DEBITED',
            $description.' TO '.$user->username.' ('.$user->agent_code.')',
            $request->user()
        );

        return back()->with('success', $message);
    }
}
