<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $view = in_array($request->input('view'), ['pl'], true) ? 'pl' : 'all';
        $limit = (int) $request->input('limit', 15);
        $limit = in_array($limit, [15, 25, 50, 100], true) ? $limit : 15;

        $query = WalletTransaction::where('user_id', $admin->id);

        if ($view === 'pl') {
            $query->whereIn('type', ['admin_credit', 'admin_debit', 'win', 'bet', 'refund']);
        }

        $transactions = $query->latest('created_at')->paginate($limit)->withQueryString();

        $summary = [
            'credit' => (float) WalletTransaction::where('user_id', $admin->id)->where('amount', '>', 0)->sum('amount'),
            'debit' => abs((float) WalletTransaction::where('user_id', $admin->id)->where('amount', '<', 0)->sum('amount')),
            'balance' => (float) $admin->balance,
        ];
        $summary['net'] = round($summary['credit'] - $summary['debit'], 2);

        return view('admin.settings.statement', compact('transactions', 'summary', 'view', 'limit'));
    }

    public function destroy(Request $request, WalletTransaction $transaction)
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);

        $transaction->delete();

        return back()->with('success', 'Statement entry deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:wallet_transactions,id'],
        ]);

        $deleted = WalletTransaction::where('user_id', $request->user()->id)
            ->whereIn('id', $data['ids'])
            ->delete();

        return back()->with('success', $deleted.' statement entr'.($deleted === 1 ? 'y' : 'ies').' deleted.');
    }
}
