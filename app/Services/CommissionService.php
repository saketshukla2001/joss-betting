<?php

namespace App\Services;

use App\Models\AgentCommission;
use App\Models\Bet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CommissionService
{
    /**
     * Accrue commission for every agent above the player.
     * Turnover agents earn rate% of the stake on settled bets.
     * Net-loss agents earn rate% of the stake only when the player loses.
     */
    public function accrueForBet(Bet $bet): int
    {
        if (! in_array($bet->status, ['won', 'lost'], true)) {
            return 0;
        }

        $player = $bet->user;

        if (! $player || ! $player->parent_id) {
            return 0;
        }

        $created = 0;

        foreach ($player->agentAncestors() as $agent) {
            $rate = round((float) $agent->commission_rate, 2);

            if ($rate <= 0) {
                continue;
            }

            if ($agent->commission_type === 'net_loss' && $bet->status !== 'lost') {
                continue;
            }

            $amount = round((float) $bet->stake * $rate / 100, 2);

            if ($amount <= 0) {
                continue;
            }

            $entry = AgentCommission::firstOrCreate(
                ['agent_id' => $agent->id, 'bet_id' => $bet->id],
                [
                    'player_id' => $player->id,
                    'stake' => $bet->stake,
                    'rate' => $rate,
                    'basis' => $agent->commission_type ?: 'turnover',
                    'amount' => $amount,
                    'status' => 'pending',
                ]
            );

            if ($entry->wasRecentlyCreated) {
                User::whereKey($agent->id)->increment('commission_balance', $amount);
                $created++;
            }
        }

        return $created;
    }

    public function backfill(): int
    {
        $created = 0;

        Bet::query()
            ->whereIn('status', ['won', 'lost'])
            ->whereHas('user', fn ($query) => $query->whereNotNull('parent_id'))
            ->with('user.parent')
            ->chunkById(200, function ($bets) use (&$created) {
                foreach ($bets as $bet) {
                    DB::transaction(function () use ($bet, &$created) {
                        $created += $this->accrueForBet($bet);
                    });
                }
            });

        return $created;
    }

    public function pendingTotal(User $agent): float
    {
        return round((float) AgentCommission::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->sum('amount'), 2);
    }

    public function payout(User $agent, User $admin): array
    {
        return DB::transaction(function () use ($agent, $admin) {
            $lockedAgent = User::whereKey($agent->id)->lockForUpdate()->firstOrFail();

            $entries = AgentCommission::where('agent_id', $lockedAgent->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            $total = round((float) $entries->sum('amount'), 2);

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'commission' => 'There is no pending commission to pay for this agent.',
                ]);
            }

            $reference = 'CMP-'.now()->format('ymd').'-'.Str::upper(Str::random(5));

            $lockedAgent->commission_balance = max(0, round((float) $lockedAgent->commission_balance - $total, 2));
            $lockedAgent->save();

            app(WalletService::class)->credit(
                $lockedAgent,
                $total,
                'admin_credit',
                'Commission payout · '.$entries->count().' entries',
                [
                    'reference_type' => 'commission',
                    'reference_id' => $lockedAgent->id,
                    'meta' => [
                        'payout_reference' => $reference,
                        'entries' => $entries->count(),
                        'paid_by' => $admin->username,
                    ],
                ]
            );

            AgentCommission::whereIn('id', $entries->pluck('id'))->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payout_reference' => $reference,
                'updated_at' => now(),
            ]);

            return ['total' => $total, 'count' => $entries->count(), 'reference' => $reference];
        });
    }
}
