<?php

namespace Tests\Feature;

use App\Models\CricketDemoMatch;
use App\Models\Market;
use App\Models\MatchGame;
use App\Models\Selection;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\BettingService;
use App\Services\Cricket\CricketBoard;
use App\Services\Cricket\Markets\CricketMarketCycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CricketAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::flushCache();
        Setting::set('cricket_feed_enabled', '1');
        Setting::set('cricket_board_enabled', '1');
        Setting::set('cricket_discovery_mode', '1');
        Setting::set('cricket_ai_markets_enabled', '1');
        Setting::set('cricket_ai_approval_required', '0');
        Setting::set('cricket_ai_use_model', '0');
        Setting::set('cricket_sportmonks_enabled', '0');
        Setting::flushCache();
        Config::set('services.cricbuzz.throttle_ms', 0);
        Config::set('services.cricbuzz.retry_backoff_ms', 0);
        Config::set('services.cricbuzz.host', 'cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.key', 'test-key');
        Config::set('services.cricbuzz.base_url', 'https://cricbuzz.test');
    }

    public function test_active_cricbuzz_play_publishes_markets_and_stumps_do_not(): void
    {
        $this->board($this->row('India', 'Australia', 'India need 40 runs', 18, 1, '3.4'));
        Http::fake();

        $summary = app(CricketMarketCycle::class)->run();

        $this->assertSame(3, $summary['generated']);
        $match = MatchGame::where('provider_match_id', '9001')->firstOrFail();
        $this->assertSame(3, $match->markets()->count());
        Http::assertNothingSent();

        Cache::forget('cricket:markets:last-generation');
        $this->board($this->row('India', 'Australia', 'Day 1: Stumps', 18, 1, '3.4', 9002));
        $again = app(CricketMarketCycle::class)->run();

        $this->assertSame(0, $again['generated']);
        $this->assertNull(MatchGame::where('provider_match_id', '9002')->first());
    }

    public function test_sportmonks_reuses_the_internal_match_for_the_same_teams(): void
    {
        $this->board($this->row('India', 'Australia', 'India need 40 runs', 18, 1, '3.4'));
        app(CricketMarketCycle::class)->run();
        $original = MatchGame::where('provider', 'cricbuzz')->firstOrFail();

        Setting::set('cricket_sportmonks_enabled', '1');
        Setting::flushCache();
        Config::set('services.sportmonks.cricket_key', 'sport-key');
        Config::set('services.sportmonks.cricket_base_url', 'https://sportmonks.test/api/v2.0');
        Cache::forget('cricket:markets:last-generation');
        Http::fake([
            'https://sportmonks.test/*' => Http::response(['data' => [[
                'id' => 555,
                'status' => '1st Innings',
                'localteam_id' => 1,
                'visitorteam_id' => 2,
                'localteam' => ['data' => ['name' => 'India']],
                'visitorteam' => ['data' => ['name' => 'Australia']],
                'league' => ['data' => ['name' => 'ODI']],
                'runs' => ['data' => [[
                    'team_id' => 1,
                    'score' => 18,
                    'wickets' => 1,
                    'overs' => 3.4,
                    'inning' => 1,
                ]]],
            ]]]),
        ]);

        app(CricketMarketCycle::class)->run();

        $this->assertSame(1, MatchGame::whereIn('provider', ['cricbuzz', 'sportmonks'])->count());
        $this->assertSame($original->id, MatchGame::where('provider', 'cricbuzz')->first()->id);
        $this->assertDatabaseHas('cricket_provider_links', [
            'provider' => 'sportmonks',
            'provider_match_id' => '555',
            'match_id' => $original->id,
        ]);
        $this->assertSame(3, $original->markets()->count());
    }

    public function test_rate_limit_keeps_the_previous_score_and_marks_it_stale(): void
    {
        Cache::put('cricbuzz:board:last', [
            'generated_at' => now()->subMinute()->toIso8601String(),
            'live' => [$this->row('India', 'Australia', 'India need 40 runs', 22, 1, '4.1')],
            'upcoming' => [],
            'results' => [],
            'errors' => [],
        ], 600);
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/matches/v1/live')) {
                return Http::response(['typeMatches' => [[
                    'seriesMatches' => [[
                        'seriesAdWrapper' => [
                            'matches' => [[
                                'matchInfo' => [
                                    'matchId' => 9001,
                                    'status' => 'India need 30 runs',
                                    'team1' => ['teamName' => 'India'],
                                    'team2' => ['teamName' => 'Australia'],
                                ],
                            ]],
                        ],
                    ]],
                ]]]);
            }

            if (str_contains($request->url(), '/livescore')) {
                return Http::response(['message' => 'rate'], 429);
            }

            return Http::response(['typeMatches' => []], 200);
        });

        $board = app(CricketBoard::class)->get(true);
        $row = $board['live'][0];

        $this->assertSame(22, $row['innings'][0]['runs']);
        $this->assertTrue($row['stale']);
        $this->assertStringContainsString('429', (string) $row['error']);
    }

    public function test_demo_match_charges_regular_wallets_and_skips_only_allowlisted_accounts(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'demo-admin',
            'email' => 'demo-admin@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
            'balance' => 5000,
        ]);
        $player = User::create([
            'name' => 'Player',
            'username' => 'demo-player',
            'email' => 'demo-player@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 5000,
        ]);
        $tester = User::create([
            'name' => 'Tester',
            'username' => 'demo-tester',
            'email' => 'demo-tester@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 5000,
        ]);
        $broke = User::create([
            'name' => 'Broke',
            'username' => 'demo-broke',
            'email' => 'demo-broke@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 50,
        ]);
        Config::set('services.cricket.demo_wallet_exempt_user_ids', [$tester->id]);
        $sport = Sport::create(['name' => 'Football', 'slug' => 'football', 'icon' => '⚽', 'active' => true]);
        $manual = MatchGame::create([
            'sport_id' => $sport->id,
            'league' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'start_time' => now()->addHour(),
            'status' => 'upcoming',
        ]);

        $this->actingAs($admin)->post(route('admin.cricket.demo.store'), [
            'team1' => 'Demo A',
            'team2' => 'Demo B',
        ])->assertRedirect();
        $demoId = CricketDemoMatch::first()->id;
        $this->actingAs($admin)->post(route('admin.cricket.demo.start', $demoId))->assertRedirect();

        app(CricketMarketCycle::class)->run();
        $demoMatch = MatchGame::withoutGlobalScope('real_money')->where('provider', 'demo')->firstOrFail();
        $this->assertSame(0, MatchGame::where('provider', 'demo')->count());
        $this->assertTrue($demoMatch->markets()->where('name', 'like', 'Will the 1st over contain a six?')->exists());

        $sixMarket = $demoMatch->markets()->where('name', 'like', 'Will the 1st over contain a six?')->firstOrFail();
        $yes = $sixMarket->selections()->where('result_key', 'yes')->firstOrFail();
        $no = $sixMarket->selections()->where('result_key', 'no')->firstOrFail();

        $placed = $this->actingAs($player)->postJson(route('bets.store'), [
            'selection_id' => $yes->id,
            'stake' => 100,
        ])->assertOk();
        $placed->assertJsonPath('balance', 4900);
        $this->assertStringContainsString('Bet placed successfully', (string) $placed->json('message'));
        $this->assertStringNotContainsString('not charged', (string) $placed->json('message'));

        $bet = $player->bets()->firstOrFail();
        $this->assertSame('pending', $bet->status);
        $this->assertSame(100.0, (float) $bet->stake);
        $this->assertSame(-100.0, (float) WalletTransaction::where('user_id', $player->id)->where('type', 'bet')->value('amount'));
        $this->assertSame(4900.0, (float) $player->fresh()->balance);
        $this->assertSame(100.0, (float) $player->fresh()->exposure);

        $testerBet = app(BettingService::class)->placeBet($tester, $yes->id, 100);
        $this->assertSame('pending', $testerBet->status);
        $this->assertSame(0, WalletTransaction::where('user_id', $tester->id)->count());
        $this->assertSame(5000.0, (float) $tester->fresh()->balance);
        $this->assertSame(0.0, (float) $tester->fresh()->exposure);

        $adminBet = app(BettingService::class)->placeBet($admin, $no->id, 100);
        $this->assertSame('pending', $adminBet->status);
        $this->assertSame(-100.0, (float) WalletTransaction::where('user_id', $admin->id)->where('type', 'bet')->value('amount'));
        $this->assertSame(4900.0, (float) $admin->fresh()->balance);

        try {
            app(BettingService::class)->placeBet($broke, $yes->id, 100);
            $this->fail('A regular account with 50 must not place a 100 stake on a demo match.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stake', $e->errors());
        }

        $this->assertSame(50.0, (float) $broke->fresh()->balance);
        $this->assertSame(0, $broke->bets()->count());
        $this->assertSame('upcoming', $manual->fresh()->status);

        foreach (['1', '4', '1', '6', '1', '1'] as $kind) {
            $this->actingAs($admin)->post(route('admin.cricket.demo.ball', $demoId), ['kind' => $kind])->assertRedirect();
        }

        Cache::forget('cricket:markets:last-generation');
        app(CricketMarketCycle::class)->run();

        $six = $demoMatch->markets()->where('name', 'like', 'Will the 1st over contain a six?')->firstOrFail();
        $this->assertSame('settled', $six->fresh()->status);
        $this->assertSame('win', $six->selections()->where('result_key', 'yes')->first()->result);

        $bet->refresh();
        $testerBet->refresh();
        $adminBet->refresh();
        $this->assertSame('won', $bet->status);
        $this->assertSame(185.0, (float) $bet->payout);
        $this->assertSame(5085.0, (float) $player->fresh()->balance);
        $this->assertSame(0.0, (float) $player->fresh()->exposure);
        $this->assertSame(185.0, (float) WalletTransaction::where('user_id', $player->id)->where('type', 'win')->value('amount'));

        $this->assertSame('won', $testerBet->status);
        $this->assertSame(0.0, (float) $testerBet->payout);
        $this->assertSame(5000.0, (float) $tester->fresh()->balance);
        $this->assertSame(0, WalletTransaction::where('user_id', $tester->id)->count());

        $this->assertSame('lost', $adminBet->status);
        $this->assertSame(0.0, (float) $adminBet->payout);
        $this->assertSame(4900.0, (float) $admin->fresh()->balance);
        $this->assertSame(0, WalletTransaction::where('user_id', $admin->id)->where('type', 'win')->count());

        $this->expectException(ValidationException::class);
        app(BettingService::class)->settleMarket($six, $yes->id);
    }

    public function test_demo_match_appears_on_the_live_cricket_board_without_replacing_cricbuzz(): void
    {
        $this->board($this->row('India', 'Australia', 'India need 40 runs', 18, 1, '3.4', 165061));
        Http::fake();

        $sport = Sport::create(['name' => 'Cricket', 'slug' => 'cricket', 'icon' => '🏏', 'active' => true]);
        $demo = CricketDemoMatch::create([
            'team1' => 'Harbour',
            'team2' => 'Marina',
            'batting' => 'Harbour',
            'status' => 'running',
            'runs' => 27,
            'wickets' => 1,
            'legal_balls' => 18,
        ]);
        $match = MatchGame::withoutGlobalScope('real_money')->create([
            'sport_id' => $sport->id,
            'league' => 'Live demo',
            'home_team' => 'Harbour',
            'away_team' => 'Marina',
            'start_time' => now(),
            'status' => 'live',
            'provider' => 'demo',
            'provider_match_id' => $demo->providerMatchId(),
        ]);
        $market = Market::create([
            'match_id' => $match->id,
            'name' => 'Will the 4th over contain 10 or more runs?',
            'status' => 'open',
        ]);
        Selection::create(['market_id' => $market->id, 'name' => 'Yes', 'result_key' => 'yes', 'odds' => 1.85, 'status' => 'active']);
        Selection::create(['market_id' => $market->id, 'name' => 'No', 'result_key' => 'no', 'odds' => 1.85, 'status' => 'active', 'sort_order' => 1]);

        $response = $this->getJson(route('cricket.board'))->assertOk();
        $live = $response->json('live');

        $this->assertSame('demo', $live[0]['provider']);
        $this->assertTrue($live[0]['demo']);
        $this->assertSame('demo', $live[0]['source']);
        $this->assertSame('Harbour vs Marina', $live[0]['label']);
        $this->assertSame(27, $live[0]['innings'][0]['runs']);
        $this->assertSame(1, $live[0]['innings'][0]['wickets']);
        $this->assertSame('3.0', $live[0]['innings'][0]['overs']);
        $this->assertSame(route('matches.show', $match), $live[0]['url']);
        $this->assertSame(165061, $live[1]['id']);
        $this->assertArrayNotHasKey('demo', $live[1]);

        $cached = Cache::get('cricbuzz:board');
        $this->assertCount(1, $cached['live']);
        $this->assertSame(165061, $cached['live'][0]['id']);
        $this->assertSame(0, MatchGame::where('provider', 'demo')->count());

        $this->get(route('matches.show', $match))
            ->assertOk()
            ->assertSee('LIVE DEMO')
            ->assertSee('Will the 4th over contain 10 or more runs?')
            ->assertSee('Yes')
            ->assertSee('No')
            ->assertSee('taken from your wallet');

        $this->get(route('demo-cricket.index'))->assertRedirect(route('cricket.index'));
        $this->get(route('demo-cricket.show', $match))->assertRedirect(route('matches.show', $match));
        $this->get(route('sports.index'))->assertDontSee('Harbour');
        Http::assertNothingSent();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function board(array $row): void
    {
        Cache::put('cricbuzz:board', [
            'generated_at' => now()->toIso8601String(),
            'live' => [$row],
            'upcoming' => [],
            'results' => [],
            'errors' => [],
        ], 120);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(string $home, string $away, string $status, int $runs, int $wickets, string $overs, int $id = 9001): array
    {
        return [
            'id' => $id,
            'provider' => 'cricbuzz',
            'label' => $home.' vs '.$away,
            'team1' => $home,
            'team2' => $away,
            'kind' => 'live',
            'status_group' => 'live',
            'status_raw' => $status,
            'status' => $status,
            'stale' => false,
            'series' => 'ODI',
            'innings' => [[
                'team' => $home,
                'runs' => $runs,
                'wickets' => $wickets,
                'overs' => $overs,
            ]],
            'fetched_at' => now()->toIso8601String(),
        ];
    }
}
