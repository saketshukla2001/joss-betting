<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->user();

        $transactions = $agent->transactions()->latest()->paginate(12);
        $withdrawals = $agent->withdrawalRequests()->latest()->limit(8)->get();

        $summary = [
            'pending_withdrawals' => (float) $agent->withdrawalRequests()->where('status', 'pending')->sum('amount'),
            'paid_commission' => (float) $agent->agentCommissions()->where('status', 'paid')->sum('amount'),
        ];

        return view('agent.wallet', compact('agent', 'transactions', 'withdrawals', 'summary'));
    }

    public function withdraw(Request $request, PaymentService $payments)
    {
        $agent = $request->user();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'method' => ['required', 'in:upi,bank,usdt'],
            'account_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:64'],
            'ifsc' => ['nullable', 'string', 'max:20', 'required_if:method,bank'],
            'upi_id' => ['nullable', 'string', 'max:120', 'required_if:method,upi'],
        ]);

        $withdrawal = $payments->requestWithdrawal($agent, $data);

        return back()->with('success', 'Withdrawal request #'.$withdrawal->id.' submitted. It will be reviewed by the admin.');
    }
}
