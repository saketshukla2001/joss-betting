<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Market;
use App\Models\MatchGame;
use App\Models\OperationLog;
use App\Models\Selection;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BettingService
{
    public function placeBet(User $user, int $selectionId, float $stake, ?string $ip = null): Bet
    {
        $stake = round($stake, 2);

        return DB::transaction(function () use ($user, $selectionId, $stake, $ip) {
            $selection = Selection::whereKey($selectionId)->lockForUpdate()->first();

            if (! $selection) {
                throw ValidationException::withMessages(['selection' => 'This selection is no longer available.']);
            }

            $market = Market::whereKey($selection->market_id)->lockForUpdate()->first();
            $match = $market ? MatchGame::whereKey($market->match_id)->lockForUpdate()->first() : null;

            if (! $market || ! $match) {
                throw ValidationException::withMessages(['selection' => 'This market is no longer available.']);
            }

            if ($market->status !== 'open' || $selection->status !== 'active') {
                throw ValidationException::withMessages(['selection' => 'Betting is suspended on this market right now.']);
            }

            if (! $match->isBettingOpen()) {
                throw ValidationException::withMessages(['selection' => 'Betting is closed for this match.']);
            }

            $min = (float) Setting::get('min_stake', 100);
            $max = (float) Setting::get('max_stake', 100000);

            if ($stake < $min) {
                throw ValidationException::withMessages(['stake' => 'Minimum stake is '.number_format($min, 0).'.']);
            }

            if ($stake > $max) {
                throw ValidationException::withMessages(['stake' => 'Maximum stake is '.number_format($max, 0).'.']);
            }

            $lockedUser = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedUser->status !== 'active') {
                throw ValidationException::withMessages(['stake' => 'Your account is not active.']);
            }

            if (round((float) $lockedUser->balance, 2) + 0.001 < $stake) {
                throw ValidationException::withMessages(['stake' => 'Insufficient balance. Please deposit funds.']);
            }

            $odds = round((float) $selection->odds, 2);

            if ($odds < 1.01) {
                throw ValidationException::withMessages(['selection' => 'Odds are unavailable for this selection.']);
            }

            $potentialReturn = round($stake * $odds, 2);

            $lockedUser->balance = round((float) $lockedUser->balance - $stake, 2);
            $lockedUser->exposure = round((float) $lockedUser->exposure + $stake, 2);
            $lockedUser->save();

            $user->setAttribute('balance', $lockedUser->balance);
            $user->setAttribute('exposure', $lockedUser->exposure);
            $user->syncOriginalAttributes(['balance', 'exposure']);

            do {
                $code = 'BZ'.now()->format('ymd').Str::upper(Str::random(5));
            } while (Bet::where('bet_code', $code)->exists());

            $bet = Bet::create([
                'bet_code' => $code,
                'user_id' => $lockedUser->id,
                'match_id' => $match->id,
                'market_id' => $market->id,
                'selection_id' => $selection->id,
                'sport_name' => $match->sport?->name ?? 'Sports',
                'league' => $match->league,
                'match_label' => $match->label,
                'market_name' => $market->name,
                'selection_name' => $selection->name,
                'odds' => $odds,
                'stake' => $stake,
                'potential_return' => $potentialReturn,
                'status' => 'pending',
                'ip_address' => $ip,
            ]);

            WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'bet',
                'amount' => -$stake,
                'balance_after' => $lockedUser->balance,
                'status' => 'completed',
                'reference_type' => 'bet',
                'reference_id' => $bet->id,
                'description' => 'Bet '.$bet->bet_code.' · '.$bet->selection_name.' @ '.number_format($odds, 2),
                'meta' => ['match' => $bet->match_label, 'market' => $bet->market_name],
            ]);

            return $bet->load(['match.sport', 'market', 'selection']);
        });
    }

    /**
     * Admin edit of an open ticket: new stake and/or new odds, and optionally a
     * different selection from the same match. Keeps the player's balance and
     * exposure in sync, rewrites the ticket snapshot fields so every screen shows
     * the correction, and leaves an audit trail.
     */
    public function adjustBet(Bet $betRef, float $stake, float $odds, ?int $selectionId = null, ?User $actor = null): Bet
    {
        $stake = round($stake, 2);
        $odds = round($odds, 2);

        $min = (float) Setting::get('min_stake', 100);
        $max = (float) Setting::get('max_stake', 100000);

        if ($stake < $min) {
            throw ValidationException::withMessages(['stake' => 'Minimum stake is '.number_format($min, 0).'.']);
        }

        if ($stake > $max) {
            throw ValidationException::withMessages(['stake' => 'Maximum stake is '.number_format($max, 0).'.']);
        }

        if ($odds < 1.01) {
            throw ValidationException::withMessages(['odds' => 'Odds must be at least 1.01.']);
        }

        return DB::transaction(function () use ($betRef, $stake, $odds, $selectionId, $actor) {
            $bet = Bet::whereKey($betRef->getKey())->lockForUpdate()->firstOrFail();

            if ($bet->status !== 'pending') {
                throw ValidationException::withMessages([
                    'bet' => 'Only open tickets can be edited. Settled and void tickets are locked.',
                ]);
            }

            $user = User::whereKey($bet->user_id)->lockForUpdate()->firstOrFail();

            $oldSelectionName = $bet->selection_name;

            if ($selectionId !== null && (int) $selectionId !== (int) $bet->selection_id) {
                $selection = Selection::with('market')->whereKey($selectionId)->first();

                if (! $selection || ! $selection->market) {
                    throw ValidationException::withMessages(['selection_id' => 'Choose a valid selection.']);
                }

                $market = $selection->market;

                if ((int) $market->match_id !== (int) $bet->match_id) {
                    throw ValidationException::withMessages([
                        'selection_id' => 'The new selection must belong to the same match. Void the ticket and place a fresh bet for another match.',
                    ]);
                }

                if ($market->status !== 'open' || $selection->status !== 'active') {
                    throw ValidationException::withMessages([
                        'selection_id' => 'That selection is not open for betting right now.',
                    ]);
                }

                $bet->selection_id = $selection->id;
                $bet->selection_name = $selection->name;
                $bet->market_id = $market->id;
                $bet->market_name = $market->name;
            }

            $oldStake = round((float) $bet->stake, 2);
            $oldOdds = round((float) $bet->odds, 2);
            $delta = round($stake - $oldStake, 2);

            if ($delta > 0 && round((float) $user->balance, 2) + 0.001 < $delta) {
                throw ValidationException::withMessages([
                    'stake' => 'Player balance ('.number_format((float) $user->balance, 2).') is not enough to raise the stake by '.number_format($delta, 2).'.',
                ]);
            }

            $user->balance = round((float) $user->balance - $delta, 2);
            $user->exposure = max(0, round((float) $user->exposure + $delta, 2));
            $user->save();

            $bet->stake = $stake;
            $bet->odds = $odds;
            $bet->potential_return = round($stake * $odds, 2);
            $bet->save();

            if ($delta !== 0.0) {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => $delta > 0 ? 'bet' : 'refund',
                    'amount' => -$delta,
                    'balance_after' => $user->balance,
                    'status' => 'completed',
                    'reference_type' => 'bet',
                    'reference_id' => $bet->id,
                    'description' => 'Admin stake edit '.$bet->bet_code.' · '.number_format($oldStake, 2).' → '.number_format($stake, 2),
                    'meta' => ['admin_id' => $actor?->id, 'old_stake' => $oldStake, 'new_stake' => $stake],
                ]);
            }

            $selectionChanged = $oldSelectionName !== $bet->selection_name;

            OperationLog::record(
                'BET_EDIT',
                'Bet '.$bet->bet_code.' edited by admin: stake '.number_format($oldStake, 2).'→'.number_format($stake, 2)
                    .', odds '.number_format($oldOdds, 2).'→'.number_format($odds, 2)
                    .($selectionChanged ? ', selection '.$oldSelectionName.'→'.$bet->selection_name : ''),
                $actor,
                [
                    'bet_id' => $bet->id,
                    'player_id' => $bet->user_id,
                    'old' => ['stake' => $oldStake, 'odds' => $oldOdds, 'selection' => $oldSelectionName],
                    'new' => ['stake' => $stake, 'odds' => $odds, 'selection' => $bet->selection_name],
                ]
            );

            return $bet->load(['user', 'match.sport', 'market', 'selection']);
        });
    }

    public function settleMarket(Market $marketRef, ?int $winningSelectionId = null, bool $void = false): int
    {
        return DB::transaction(function () use ($marketRef, $winningSelectionId, $void) {
            $market = Market::whereKey($marketRef->getKey())->lockForUpdate()->firstOrFail();

            if (in_array($market->status, ['settled', 'void'], true)) {
                throw ValidationException::withMessages(['market' => 'This market is already settled.']);
            }

            $match = MatchGame::whereKey($market->match_id)->lockForUpdate()->firstOrFail();
            $selections = Selection::where('market_id', $market->id)->lockForUpdate()->get();
            $bets = Bet::where('market_id', $market->id)->where('status', 'pending')->lockForUpdate()->get();

            if ($void) {
                foreach ($selections as $selection) {
                    $selection->update(['result' => 'void', 'status' => 'suspended']);
                }

                foreach ($bets as $bet) {
                    $this->refundBet($bet);
                }

                $market->update(['status' => 'void']);
            } else {
                $winner = $selections->firstWhere('id', $winningSelectionId);

                if (! $winner) {
                    throw ValidationException::withMessages(['selection' => 'Choose a valid winning selection.']);
                }

                foreach ($selections as $selection) {
                    $selection->update(['result' => $selection->id === $winner->id ? 'win' : 'lose']);
                }

                foreach ($bets as $bet) {
                    if ($bet->selection_id === $winner->id) {
                        $this->payBet($bet);
                    } else {
                        $this->loseBet($bet);
                    }
                }

                $market->update(['status' => 'settled']);
            }

            $this->refreshMatchStatus($match);
            Setting::flushCache();

            return $bets->count();
        });
    }

    public function declareResult(MatchGame $matchRef, string $result): int
    {
        if (! in_array($result, ['home', 'away', 'draw'], true)) {
            throw ValidationException::withMessages(['result' => 'Invalid match result.']);
        }

        return DB::transaction(function () use ($matchRef, $result) {
            $match = MatchGame::whereKey($matchRef->getKey())->lockForUpdate()->firstOrFail();
            $match->result = $result;
            $match->save();

            $settled = 0;

            $markets = Market::where('match_id', $match->id)
                ->where('auto_settle', true)
                ->whereIn('status', ['open', 'suspended'])
                ->get();

            foreach ($markets as $market) {
                $winner = Selection::where('market_id', $market->id)
                    ->where('result_key', $result)
                    ->first();

                $settled += $this->settleMarket($market, $winner?->id, $winner === null);
            }

            $this->refreshMatchStatus($match);

            return $settled;
        });
    }

    public function voidMatch(MatchGame $matchRef): int
    {
        return DB::transaction(function () use ($matchRef) {
            $match = MatchGame::whereKey($matchRef->getKey())->lockForUpdate()->firstOrFail();

            if ($match->status === 'cancelled') {
                return 0;
            }

            $markets = Market::where('match_id', $match->id)
                ->whereIn('status', ['open', 'suspended'])
                ->get();

            $settled = 0;

            foreach ($markets as $market) {
                $settled += $this->settleMarket($market, null, true);
            }

            $match->update(['status' => 'cancelled', 'result' => null]);

            return $settled;
        });
    }

    private function payBet(Bet $bet): void
    {
        $payout = round($bet->stake * $bet->odds, 2);
        $user = User::whereKey($bet->user_id)->lockForUpdate()->first();

        if ($user) {
            $user->balance = round((float) $user->balance + $payout, 2);
            $user->exposure = max(0, round((float) $user->exposure - $bet->stake, 2));
            $user->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'win',
                'amount' => $payout,
                'balance_after' => $user->balance,
                'status' => 'completed',
                'reference_type' => 'bet',
                'reference_id' => $bet->id,
                'description' => 'Bet won '.$bet->bet_code.' · '.$bet->selection_name,
                'meta' => ['odds' => $bet->odds, 'stake' => $bet->stake],
            ]);
        }

        $bet->update(['status' => 'won', 'payout' => $payout, 'settled_at' => now()]);

        app(CommissionService::class)->accrueForBet($bet->fresh());
    }

    private function loseBet(Bet $bet): void
    {
        $user = User::whereKey($bet->user_id)->lockForUpdate()->first();

        if ($user) {
            $user->exposure = max(0, round((float) $user->exposure - $bet->stake, 2));
            $user->save();
        }

        $bet->update(['status' => 'lost', 'payout' => 0, 'settled_at' => now()]);

        app(CommissionService::class)->accrueForBet($bet->fresh());
    }

    private function refundBet(Bet $bet): void
    {
        $user = User::whereKey($bet->user_id)->lockForUpdate()->first();

        if ($user) {
            $user->balance = round((float) $user->balance + $bet->stake, 2);
            $user->exposure = max(0, round((float) $user->exposure - $bet->stake, 2));
            $user->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $bet->stake,
                'balance_after' => $user->balance,
                'status' => 'completed',
                'reference_type' => 'bet',
                'reference_id' => $bet->id,
                'description' => 'Bet voided '.$bet->bet_code.' · stake refunded',
                'meta' => ['match' => $bet->match_label],
            ]);
        }

        $bet->update(['status' => 'void', 'payout' => $bet->stake, 'settled_at' => now()]);
    }

    private function refreshMatchStatus(MatchGame $match): void
    {
        if ($match->status === 'cancelled') {
            return;
        }

        $hasOpenMarkets = Market::where('match_id', $match->id)
            ->whereIn('status', ['open', 'suspended'])
            ->exists();

        if (! $hasOpenMarkets) {
            $match->status = 'settled';
            $match->save();
        }
    }
}
