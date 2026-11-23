<?php

namespace App\Services;

use App\Models\DepositRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function requestDeposit(User $user, array $data): DepositRequest
    {
        $min = (float) Setting::get('min_deposit', 500);

        if ((float) $data['amount'] < $min) {
            throw ValidationException::withMessages(['amount' => 'Minimum deposit is '.number_format($min, 0).'.']);
        }

        return DepositRequest::create([
            'user_id' => $user->id,
            'amount' => round((float) $data['amount'], 2),
            'method' => $data['method'],
            'transaction_ref' => $data['transaction_ref'],
            'status' => 'pending',
        ]);
    }

    public function approveDeposit(DepositRequest $requestRef, User $admin, ?string $note = null): DepositRequest
    {
        return DB::transaction(function () use ($requestRef, $admin, $note) {
            $request = DepositRequest::whereKey($requestRef->getKey())->lockForUpdate()->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['deposit' => 'This deposit request has already been reviewed.']);
            }

            app(WalletService::class)->credit(
                User::findOrFail($request->user_id),
                (float) $request->amount,
                'deposit',
                'Deposit approved #'.$request->id.' · '.$request->method_label,
                ['reference_type' => 'deposit_request', 'reference_id' => $request->id]
            );

            $request->update([
                'status' => 'approved',
                'admin_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $request;
        });
    }

    public function rejectDeposit(DepositRequest $requestRef, User $admin, ?string $note = null): DepositRequest
    {
        return DB::transaction(function () use ($requestRef, $admin, $note) {
            $request = DepositRequest::whereKey($requestRef->getKey())->lockForUpdate()->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['deposit' => 'This deposit request has already been reviewed.']);
            }

            $request->update([
                'status' => 'rejected',
                'admin_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $request;
        });
    }

    public function requestWithdrawal(User $user, array $data): WithdrawalRequest
    {
        $amount = round((float) $data['amount'], 2);
        $min = (float) Setting::get('min_withdrawal', 100);

        if ($amount < $min) {
            throw ValidationException::withMessages(['amount' => 'Minimum withdrawal is '.number_format($min, 0).'.']);
        }

        return DB::transaction(function () use ($user, $data, $amount) {
            $locked = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if (round((float) $locked->balance, 2) < $amount) {
                throw ValidationException::withMessages(['amount' => 'Withdrawal amount exceeds your available balance.']);
            }

            $locked->balance = round((float) $locked->balance - $amount, 2);
            $locked->save();

            $request = WithdrawalRequest::create([
                'user_id' => $locked->id,
                'amount' => $amount,
                'method' => $data['method'],
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'ifsc' => $data['ifsc'] ?? null,
                'upi_id' => $data['upi_id'] ?? null,
                'status' => 'pending',
            ]);

            WalletTransaction::create([
                'user_id' => $locked->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'balance_after' => $locked->balance,
                'status' => 'pending',
                'reference_type' => 'withdrawal_request',
                'reference_id' => $request->id,
                'description' => 'Withdrawal requested #'.$request->id.' · '.$request->method_label,
                'meta' => [
                    'account_name' => $request->account_name,
                    'account_number' => $request->account_number,
                ],
            ]);

            return $request;
        });
    }

    public function approveWithdrawal(WithdrawalRequest $requestRef, User $admin, ?string $note = null): WithdrawalRequest
    {
        return DB::transaction(function () use ($requestRef, $admin, $note) {
            $request = WithdrawalRequest::whereKey($requestRef->getKey())->lockForUpdate()->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['withdrawal' => 'This withdrawal request has already been reviewed.']);
            }

            WalletTransaction::where('reference_type', 'withdrawal_request')
                ->where('reference_id', $request->id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->update(['status' => 'completed']);

            $request->update([
                'status' => 'approved',
                'admin_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $request;
        });
    }

    public function rejectWithdrawal(WithdrawalRequest $requestRef, User $admin, ?string $note = null): WithdrawalRequest
    {
        return DB::transaction(function () use ($requestRef, $admin, $note) {
            $request = WithdrawalRequest::whereKey($requestRef->getKey())->lockForUpdate()->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['withdrawal' => 'This withdrawal request has already been reviewed.']);
            }

            WalletTransaction::where('reference_type', 'withdrawal_request')
                ->where('reference_id', $request->id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            app(WalletService::class)->credit(
                User::findOrFail($request->user_id),
                (float) $request->amount,
                'refund',
                'Withdrawal rejected #'.$request->id.' · amount returned',
                ['reference_type' => 'withdrawal_request', 'reference_id' => $request->id]
            );

            $request->update([
                'status' => 'rejected',
                'admin_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $request;
        });
    }
}
