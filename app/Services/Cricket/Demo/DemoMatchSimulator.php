<?php

namespace App\Services\Cricket\Demo;

use App\Models\CricketDemoBall;
use App\Models\CricketDemoMatch;
use App\Models\MatchGame;
use App\Support\PlayState;
use Illuminate\Validation\ValidationException;

/**
 * Isolated scoreboard for client testing. It writes only demo tables.
 * Real wallets and real provider matches are not touched here.
 */
final class DemoMatchSimulator
{
    /**
     * @return array<int, string>
     */
    public function kinds(): array
    {
        return ['0', '1', '2', '3', '4', '6', 'w', 'wd', 'nb', 'bye', 'lb'];
    }

    public function create(string $team1, string $team2): CricketDemoMatch
    {
        return CricketDemoMatch::create([
            'team1' => $team1,
            'team2' => $team2,
            'batting' => $team1,
            'status' => 'ready',
        ]);
    }

    public function start(CricketDemoMatch $match): void
    {
        if ($match->wickets >= 10) {
            throw ValidationException::withMessages(['demo' => 'This demo innings is already over.']);
        }

        $match->update(['status' => 'running']);
    }

    public function pause(CricketDemoMatch $match): void
    {
        $match->update(['status' => 'paused']);
    }

    public function resume(CricketDemoMatch $match): void
    {
        $this->start($match);
    }

    public function bowl(CricketDemoMatch $match, string $kind): CricketDemoBall
    {
        if ($match->status !== 'running') {
            throw ValidationException::withMessages(['demo' => 'Start the demo before bowling.']);
        }

        if (! in_array($kind, $this->kinds(), true)) {
            throw ValidationException::withMessages(['demo' => 'That ball type is not supported.']);
        }

        if ($match->wickets >= 10) {
            $match->update(['status' => 'finished', 'auto_bowl' => false]);
            throw ValidationException::withMessages(['demo' => 'The demo innings is complete.']);
        }

        $legal = ! in_array($kind, ['wd', 'nb'], true);
        $wicket = $kind === 'w';
        $runs = match ($kind) {
            'wd', 'nb', 'bye', 'lb' => 1,
            'w' => 0,
            default => (int) $kind,
        };

        $seq = (int) $match->balls()->max('seq') + 1;

        $ball = $match->balls()->create([
            'seq' => $seq,
            'kind' => $kind,
            'runs' => $runs,
            'legal' => $legal,
            'wicket' => $wicket,
        ]);

        $match->runs += $runs;
        $match->legal_balls += $legal ? 1 : 0;
        $match->wickets += $wicket ? 1 : 0;

        if ($match->wickets >= 10) {
            $match->status = 'finished';
            $match->auto_bowl = false;
        }

        $match->save();

        return $ball;
    }

    public function tick(): void
    {
        CricketDemoMatch::where('status', 'running')->where('auto_bowl', true)->get()->each(function (CricketDemoMatch $match) {
            $script = ['1', '0', '4', '1', 'wd', '2', '6', 'w', '1', '0'];
            $index = (int) $match->balls()->count() % count($script);
            $this->bowl($match, $script[$index]);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function boardRows(): array
    {
        return CricketDemoMatch::with('balls')->whereIn('status', ['running', 'paused', 'finished'])->get()->map(function (CricketDemoMatch $match) {
            $legal = (int) $match->legal_balls;
            $completed = intdiv($legal, 6);
            $remainder = $legal % 6;
            $overs = $remainder === 0 ? $completed.'.0' : $completed.'.'.$remainder;
            $play = match ($match->status) {
                'running' => 'Live',
                'paused' => 'Interrupted',
                default => 'Finished',
            };

            return [
                'id' => $match->providerMatchId(),
                'provider' => 'demo',
                'label' => $match->team1.' vs '.$match->team2,
                'team1' => $match->team1,
                'team2' => $match->team2,
                'kind' => $match->status === 'finished' ? 'result' : 'live',
                'status_group' => $match->status === 'finished' ? 'completed' : 'live',
                'status' => $play,
                'status_raw' => $play,
                'status_label' => $match->status === 'running' ? 'Live demo' : ucfirst($match->status).' demo',
                'play_state' => match ($match->status) {
                    'running' => PlayState::IN_PLAY,
                    'paused' => PlayState::INTERRUPTED,
                    default => PlayState::COMPLETED,
                },
                'stale' => false,
                'series' => 'Live demo',
                'ball_feed' => true,
                'balls' => $match->balls->map(fn (CricketDemoBall $ball) => [
                    'seq' => $ball->seq,
                    'kind' => $ball->kind,
                    'runs' => $ball->runs,
                    'legal' => $ball->legal,
                    'wicket' => $ball->wicket,
                ])->all(),
                'innings' => [[
                    'team' => $match->batting,
                    'runs' => $match->runs,
                    'wickets' => $match->wickets,
                    'overs' => $overs,
                ]],
                'fetched_at' => now()->toIso8601String(),
            ];
        })->all();
    }

    /**
     * Cards for the public live-cricket board. Same shape as a provider row,
     * but the source stays demo and the link opens the existing sportsbook match.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listingRows(): array
    {
        $rows = $this->boardRows();
        $ids = array_map(fn (array $row) => (string) $row['id'], $rows);

        $matches = MatchGame::withoutGlobalScope('real_money')
            ->where('provider', 'demo')
            ->whereIn('provider_match_id', $ids)
            ->get()
            ->keyBy('provider_match_id');

        $cards = [];

        foreach ($rows as $row) {
            $match = $matches->get((string) $row['id']);

            if (! $match) {
                continue;
            }

            unset($row['balls']);

            $row['demo'] = true;
            $row['source'] = 'demo';
            $row['sportsbook_match_id'] = $match->id;
            $row['url'] = route('matches.show', $match);
            $row['status_label'] = $match->status === 'live' || $row['kind'] === 'live' ? 'Live' : 'Finished';
            $row['status_raw'] = 'Simulated score';
            $cards[] = $row;
        }

        return $cards;
    }
}
