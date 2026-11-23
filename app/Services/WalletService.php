<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function credit(User $user, float $amount, string $type, string $description, array $options = []): WalletTransaction
    {
        return $this->apply($user, abs($amount), $type, $description, $options);
    }

    public function debit(User $user, float $amount, string $type, string $description, array $options = []): WalletTransaction
    {
        return $this->apply($user, -abs($amount), $type, $description, $options);
    }

    private function apply(User $user, float $amount, string $type, string $description, array $options): WalletTransaction
    {
        $amount = round($amount, 2);

        if (abs($amount) < 0.01) {
            throw ValidationException::withMessages(['amount' => 'Amount must be greater than zero.']);
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $options) {
            $locked = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $newBalance = round((float) $locked->balance + $amount, 2);

            if ($newBalance < 0) {
                throw ValidationException::withMessages(['amount' => 'Insufficient wallet balance for this operation.']);
            }

            $locked->balance = $newBalance;
            $locked->save();

            $user->setAttribute('balance', $newBalance);
            $user->syncOriginalAttribute('balance');

            return WalletTransaction::create([
                'user_id' => $locked->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'status' => $options['status'] ?? 'completed',
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'description' => $description,
                'meta' => $options['meta'] ?? null,
            ]);
        });
    }
}
