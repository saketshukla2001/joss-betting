<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;

class AccountOperationsController extends Controller
{
    public function index(Request $request)
    {
        $logs = OperationLog::latest('created_at')->latest('id')->paginate(15)->withQueryString();

        return view('admin.settings.account-operations', compact('logs'));
    }

    public function adjust(Request $request, WalletService $wallet)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'direction' => ['required', 'in:credit,debit'],
            'note' => ['required', 'string', 'min:3', 'max:200'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $description = $data['note'].' · by '.$request->user()->username;

        if ($data['direction'] === 'credit') {
            $wallet->credit($user, (float) $data['amount'], 'admin_credit', $description, [
                'reference_type' => 'account_operation', 'reference_id' => $request->user()->id,
            ]);
            $message = 'Credited '.number_format((float) $data['amount'], 0).' coins to '.$user->username.'.';
        } else {
            $wallet->debit($user, (float) $data['amount'], 'admin_debit', $description, [
                'reference_type' => 'account_operation', 'reference_id' => $request->user()->id,
            ]);
            $message = 'Debited '.number_format((float) $data['amount'], 0).' coins from '.$user->username.'.';
        }

        OperationLog::record(
            $data['direction'] === 'credit' ? 'COINS CREDITED' : 'COINS DEBITED',
            $description.' TO '.$user->username.' ('.$user->agent_code.')',
            $request->user()
        );

        return back()->with('success', $message);
    }
}
