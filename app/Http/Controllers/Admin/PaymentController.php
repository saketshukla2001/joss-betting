<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\WithdrawalRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function deposits(Request $request)
    {
        $query = DepositRequest::with(['user', 'reviewer']);

        if (in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->whereHas('user', fn ($q) => $q->where('username', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        $summary = [
            'pending' => DepositRequest::where('status', 'pending')->count(),
            'pending_amount' => (float) DepositRequest::where('status', 'pending')->sum('amount'),
            'approved_amount' => (float) DepositRequest::where('status', 'approved')->sum('amount'),
            'total' => DepositRequest::count(),
        ];

        $deposits = $query->latest()->paginate(15)->withQueryString();

        return view('admin.payments.deposits', compact('deposits', 'summary'));
    }

    public function approveDeposit(Request $request, DepositRequest $deposit, PaymentService $payments)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:200']]);

        $payments->approveDeposit($deposit, Auth::user(), $data['admin_note'] ?? null);

        return back()->with('success', 'Deposit #'.$deposit->id.' approved and wallet credited.');
    }

    public function rejectDeposit(Request $request, DepositRequest $deposit, PaymentService $payments)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:200']]);

        $payments->rejectDeposit($deposit, Auth::user(), $data['admin_note'] ?? null);

        return back()->with('success', 'Deposit #'.$deposit->id.' rejected.');
    }

    public function withdrawals(Request $request)
    {
        $query = WithdrawalRequest::with(['user', 'reviewer']);

        if (in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->whereHas('user', fn ($q) => $q->where('username', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        $summary = [
            'pending' => WithdrawalRequest::where('status', 'pending')->count(),
            'pending_amount' => (float) WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'approved_amount' => (float) WithdrawalRequest::where('status', 'approved')->sum('amount'),
            'total' => WithdrawalRequest::count(),
        ];

        $withdrawals = $query->latest()->paginate(15)->withQueryString();

        return view('admin.payments.withdrawals', compact('withdrawals', 'summary'));
    }

    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawal, PaymentService $payments)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:200']]);

        $payments->approveWithdrawal($withdrawal, Auth::user(), $data['admin_note'] ?? null);

        return back()->with('success', 'Withdrawal #'.$withdrawal->id.' approved.');
    }

    public function rejectWithdrawal(Request $request, WithdrawalRequest $withdrawal, PaymentService $payments)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:200']]);

        $payments->rejectWithdrawal($withdrawal, Auth::user(), $data['admin_note'] ?? null);

        return back()->with('success', 'Withdrawal #'.$withdrawal->id.' rejected and amount refunded.');
    }
}
