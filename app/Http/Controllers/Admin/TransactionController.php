<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public const TYPES = [
        'deposit', 'withdrawal', 'bet', 'win', 'refund', 'admin_credit', 'admin_debit', 'bonus',
    ];

    public function index(Request $request)
    {
        $query = WalletTransaction::with('user');

        if (in_array($request->input('type'), self::TYPES, true)) {
            $query->where('type', $request->input('type'));
        }

        if (in_array($request->input('status'), ['completed', 'pending', 'rejected'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $summary = [
            'credit' => (float) (clone $query)->where('amount', '>', 0)->sum('amount'),
            'debit' => abs((float) (clone $query)->where('amount', '<', 0)->sum('amount')),
            'count' => (clone $query)->count(),
        ];

        $transactions = $query->latest()->paginate(20)->withQueryString();
        $types = self::TYPES;

        return view('admin.transactions.index', compact('transactions', 'summary', 'types'));
    }
}
