<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = $user->transactions()->latest()->paginate(15);
        $deposits = $user->depositRequests()->latest()->limit(6)->get();
        $withdrawals = $user->withdrawalRequests()->latest()->limit(6)->get();

        $summary = [
            'deposits' => (float) $user->transactions()->where('type', 'deposit')->where('status', 'completed')->sum('amount'),
            'withdrawals' => abs((float) $user->transactions()->where('type', 'withdrawal')->sum('amount')),
            'winnings' => (float) $user->transactions()->where('type', 'win')->sum('amount'),
            'pending_withdrawals' => (float) $user->withdrawalRequests()->where('status', 'pending')->sum('amount'),
        ];

        return view('wallet.index', compact('user', 'transactions', 'deposits', 'withdrawals', 'summary'));
    }

    public function deposit(Request $request, PaymentService $payments)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'method' => ['required', 'in:upi,bank,usdt'],
            'transaction_ref' => ['required', 'string', 'max:120'],
        ]);

        $deposit = $payments->requestDeposit($request->user(), $data);

        return back()->with('success', 'Deposit request #'.$deposit->id.' submitted. It will be credited after admin approval.');
    }

    public function withdraw(Request $request, PaymentService $payments)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'method' => ['required', 'in:upi,bank,usdt'],
            'account_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:64'],
            'ifsc' => ['nullable', 'string', 'max:20', 'required_if:method,bank'],
            'upi_id' => ['nullable', 'string', 'max:120', 'required_if:method,upi'],
        ]);

        $withdrawal = $payments->requestWithdrawal($request->user(), $data);

        return back()->with('success', 'Withdrawal request #'.$withdrawal->id.' submitted. Funds are on hold until approval.');
    }
}
