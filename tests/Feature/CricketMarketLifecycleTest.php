<?php

namespace Tests\Feature;

use App\Models\CricketQuestionCandidate;
use App\Models\Market;
use App\Models\MatchGame;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use App\Services\BettingService;
use App\Services\Cricket\Markets\CricketMarketCycle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CricketMarketLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::flushCache();
        Setting::set('cricket_feed_enabled', '1');
        Setting::set('cricket_board_enabled', '1');
        Setting::set('cricket_ai_markets_enabled', '1');
        Setting::set('cricket_ai_approval_required', '0');
        Setting::set('cricket_ai_use_model', '0');
        Setting::set('min_stake', '100');
        Setting::flushCache();
    }

    public function test_markets_are_not_created_when_generation_is_off(): void
    {
        Setting::set('cricket_ai_markets_enabled', '0');
        Setting::flushCache();
        $this->board($this->liveRow());
        Http::fake();

        $summary = app(CricketMarketCycle::class)->run();

        $this->assertSame('Automatic cricket markets are turned off. Demo matches still run.', $summary['skipped']);
        $this->assertSame(0, Market::count());
        Http::assertNothingSent();
    }

    public function test_live_score_opens_yes_no_markets_and_a_client_can_bet_them(): void
    {
        $this->board($this->liveRow());
        Http::fake();

        $summary = app(CricketMarketCycle::class)->run();

        $this->assertSame(3, $summary['generated']);
        $this->assertSame(0, $summary['held']);
        Http::assertNothingSent();

        $match = MatchGame::where('provider_match_id', '9001')->firstOrFail();
        $this->assertSame('live', $match->status);
        $this->assertSame('cricket', $match->sport->slug);

        $names = $match->markets()->pluck('name')->all();
        $this->assertContains('Will India score 20 or more runs by the end of the 4th over?', $names);
        $this->assertContains('Will the 5th over contain 10 or more runs?', $names);
        $this->assertContains('Will a wicket fall in the 5th over?', $names);
        $this->assertCount(3, $names);

        $market = $match->markets()->where('name', 'like', 'Will India score 20%')->firstOrFail();
        $this->assertFalse($market->auto_settle);
        $this->assertEqualsCanonicalizing(['yes', 'no'], $market->selections()->pluck('result_key')->all());

        $again = app(CricketMarketCycle::class)->run();
        $this->assertSame(0, $again['generated']);
        $this->assertSame(3, $match->markets()->count());

        $player = $this->client();
        $yes = $market->selections()->where('result_key', 'yes')->firstOrFail();
        $bet = app(BettingService::class)->placeBet($player, $yes->id, 100);

        $this->assertSame('pending', $bet->status);
        $this->assertSame(1.85, (float) $bet->odds);
        $this->assertSame(4900.0, (float) $player->fresh()->balance);

        $this->get(route('matches.show', $match))->assertOk()->assertSee('Will India score 20 or more runs');
        $this->getJson(route('cricket.markets.feed'))
            ->assertOk()
            ->assertJsonPath('matches.0.runs', 18)
            ->assertJsonPath('matches.0.overs', '3.4');
        Http::assertNothingSent();
    }

    public function test_threshold_settles_yes_and_the_over_boundary_settles_no(): void
    {
        $this->board($this->liveRow());
        app(CricketMarketCycle::class)->run();
        $market = Market::where('name', 'like', 'Will India score 20%')->firstOrFail();
        $yes = $market->selections()->where('result_key', 'yes')->firstOrFail();
        $player = $this->client();
        app(BettingService::class)->placeBet($player, $yes->id, 100);

        $this->board($this->liveRow(20, 0, '3.5'));
        Cache::put('cricket:markets:last-generation', now()->timestamp, 3600);
        app(CricketMarketCycle::class)->run();

        $this->assertSame('won', $player->bets()->first()->status);
        $this->assertSame('settled', $market->fresh()->status);
        $this->assertSame(4900 + 185.0, (float) $player->fresh()->balance);

        $this->board($this->liveRow(runs: 18, wickets: 0, overs: '3.4', id: 9002));
        Cache::forget('cricket:markets:last-generation');
        app(CricketMarketCycle::class)->run();
        $other = Market::where('name', 'like', 'Will India score 20%')->where('id', '!=', $market->id)->firstOrFail();
        $noPlayer = $this->client();
        app(BettingService::class)->placeBet($noPlayer, $other->selections()->where('result_key', 'yes')->first()->id, 100);

        $this->board($this->liveRow(19, 0, '4.0', 9002));
        Cache::put('cricket:markets:last-generation', now()->timestamp, 3600);
        app(CricketMarketCycle::class)->run();

        $this->assertSame('lost', $noPlayer->bets()->first()->status);
        $this->assertSame('settled', $other->fresh()->status);
        $this->assertSame('win', $other->selections()->where('result_key', 'no')->first()->result);
        $this->assertSame(4900.0, (float) $noPlayer->fresh()->balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $noPlayer->id,
            'type' => 'bet',
            'amount' => -100,
        ]);
    }

    public function test_next_over_market_uses_observed_boundaries_and_voids_when_the_feed_skips(): void
    {
        $this->board($this->liveRow());
        app(CricketMarketCycle::class)->run();
        $runs = Market::where('name', 'Will the 5th over contain 10 or more runs?')->firstOrFail();
        $wicket = Market::where('name', 'Will a wicket fall in the 5th over?')->firstOrFail();

        $this->board($this->liveRow(22, 0, '4.0'));
        Cache::put('cricket:markets:last-generation', now()->timestamp, 3600);
        app(CricketMarketCycle::class)->run();

        $this->assertSame('open', $runs->fresh()->status);
        $this->assertSame(22, $runs->cricketRule->fresh()->snapshot['start_runs']);
        $this->assertSame(0, $runs->cricketRule->snapshot['start_wickets']);

        $player = $this->client();
        $yes = $runs->selections()->where('result_key', 'yes')->firstOrFail();
        app(BettingService::class)->placeBet($player, $yes->id, 100);

        $this->board($this->liveRow(24, 0, '4.1'));
        app(CricketMarketCycle::class)->run();

        $this->assertSame('suspended', $runs->fresh()->status);
        $this->assertSame('awaiting', $runs->cricketRule->fresh()->phase);

        try {
            app(BettingService::class)->placeBet($this->client(), $yes->id, 100);
            $this->fail('A bet was accepted after the over started.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('suspended', $e->validator->errors()->first());
        }

        $this->board($this->liveRow(33, 1, '5.0'));
        app(CricketMarketCycle::class)->run();

        $this->assertSame('settled', $runs->fresh()->status);
        $this->assertSame('win', $runs->selections()->where('result_key', 'yes')->first()->result);
        $this->assertSame('lose', $runs->selections()->where('result_key', 'no')->first()->result);
        $this->assertSame('win', $wicket->fresh()->selections()->where('result_key', 'yes')->first()->result);
        $this->assertSame('won', $player->bets()->first()->status);
        $this->assertSame(4900 + 185.0, (float) $player->fresh()->balance);

        app(CricketMarketCycle::class)->run();
        $this->assertSame(4900 + 185.0, (float) $player->fresh()->balance);
        $this->assertSame(1, $player->bets()->count());

        $this->board($this->liveRow(18, 0, '3.4', 9003));
        Cache::forget('cricket:markets:last-generation');
        app(CricketMarketCycle::class)->run();
        $skipped = Market::where('name', 'Will the 5th over contain 10 or more runs?')
            ->whereHas('match', fn ($query) => $query->where('provider_match_id', '9003'))
            ->firstOrFail();

        $this->board($this->liveRow(30, 0, '4.1', 9003));
        Cache::put('cricket:markets:last-generation', now()->timestamp, 3600);
        app(CricketMarketCycle::class)->run();

        $this->assertSame('void', $skipped->fresh()->status);
        $this->assertSame('void', $skipped->cricketRule->fresh()->phase);
    }

    public function test_existing_manual_market_is_left_alone(): void
    {
        $sport = Sport::create(['name' => 'Football', 'slug' => 'football', 'icon' => '⚽', 'active' => true]);
        $manual = MatchGame::create([
            'sport_id' => $sport->id,
            'league' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'start_time' => now()->addHour(),
            'status' => 'upcoming',
        ]);
        $market = $manual->markets()->create(['name' => 'Match Winner', 'auto_settle' => true, 'status' => 'open', 'sort_order' => 1]);
        $market->selections()->create(['name' => 'Arsenal', 'result_key' => 'home', 'odds' => 2.10, 'status' => 'active', 'sort_order' => 1]);

        $this->board($this->liveRow());
        app(CricketMarketCycle::class)->run();

        $this->assertSame('open', $market->fresh()->status);
        $this->assertNull($market->cricketRule);
        $this->assertSame('upcoming', $manual->fresh()->status);
    }

    public function test_model_suggestions_are_validated_and_do_not_settle(): void
    {
        Setting::set('cricket_ai_use_model', '1');
        Setting::flushCache();
        config([
            'services.cricket_ai.key' => 'test-key',
            'services.cricket_ai.url' => 'https://ai.test/v1/chat/completions',
        ]);

        $parameters = [
            'innings' => 1,
            'target_over' => 4,
            'target_ball' => null,
            'threshold' => 20,
            'batting_team' => 'India',
            'trigger' => 'over_end',
            'close_rule' => 'end_of_over_or_threshold',
            'resolve_rule' => 'runs_at_boundary_gte_threshold',
        ];

        Http::fake([
            'https://ai.test/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'questions' => [
                                ['template' => 'runs_by_over', 'parameters' => $parameters],
                                ['template' => 'invented_winner', 'parameters' => $parameters],
                            ],
                        ]),
                    ],
                ]],
            ]),
        ]);

        $this->board($this->liveRow());
        $summary = app(CricketMarketCycle::class)->run();

        $this->assertSame(1, $summary['generated']);
        $market = Market::firstOrFail();
        $this->assertSame('open', $market->status);
        $this->assertSame('ai', $market->cricketRule->source);
        $this->assertSame('pending', $market->bets()->exists() ? 'has-bets' : 'pending');
        Http::assertSentCount(1);
    }

    public function test_approval_holds_a_question_until_an_admin_accepts_it(): void
    {
        Setting::set('cricket_ai_approval_required', '1');
        Setting::flushCache();
        $this->board($this->liveRow());

        $summary = app(CricketMarketCycle::class)->run();

        $this->assertSame(0, $summary['generated']);
        $this->assertSame(3, $summary['held']);
        $this->assertSame(0, Market::count());

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin1',
            'email' => 'admin1@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get(route('admin.cricket.markets'))->assertOk()->assertSee('Will India score 20');

        $candidate = CricketQuestionCandidate::where('template', 'runs_by_over')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.cricket.markets.approve', $candidate))->assertRedirect();

        $this->assertSame(1, Market::count());
        $this->assertSame('published', $candidate->fresh()->status);

        $agent = User::create([
            'name' => 'Agent',
            'username' => 'agent1',
            'email' => 'agent1@example.test',
            'password' => 'Password@123',
            'role' => 'agent',
            'status' => 'active',
            'balance' => 1000,
        ]);

        $match = MatchGame::where('provider_match_id', '9001')->firstOrFail();
        $this->actingAs($agent)->get(route('agent.matches.show', $match))
            ->assertOk()
            ->assertSee('WILL INDIA SCORE 20 OR MORE RUNS');
    }

    public function test_rejected_questions_are_not_published_again(): void
    {
        Setting::set('cricket_ai_approval_required', '1');
        Setting::flushCache();
        $this->board($this->liveRow());
        app(CricketMarketCycle::class)->run();

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin2',
            'email' => 'admin2@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $candidate = CricketQuestionCandidate::firstOrFail();
        $this->actingAs($admin)->post(route('admin.cricket.markets.reject', $candidate))->assertRedirect();

        Cache::forget('cricket:markets:last-generation');
        app(CricketMarketCycle::class)->run();

        $this->assertSame(0, Market::count());
        $this->assertSame(1, CricketQuestionCandidate::where('fingerprint', $candidate->fingerprint)->count());
        $this->assertSame('rejected', $candidate->fresh()->status);
        $this->actingAs($admin)->post(route('admin.cricket.markets.approve', $candidate))->assertSessionHasErrors('candidate');

        $client = $this->client();
        $this->actingAs($client)->post(route('admin.cricket.markets.approve', $candidate))->assertForbidden();
    }

    public function test_settings_persist_and_bad_model_output_falls_back_to_templates(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin3',
            'email' => 'admin3@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.cricket.markets.settings'), [
            'enabled' => '1',
            'approval_required' => '1',
            'generation_seconds' => 90,
            'yes_odds' => '1.91',
            'no_odds' => '1.87',
            'templates' => ['runs_by_over', 'six_in_over'],
        ])->assertRedirect();

        Config::set('services.cricbuzz.throttle_ms', 0);
        Config::set('services.cricbuzz.retry_backoff_ms', 0);
        Http::fake();

        $this->actingAs($admin)->get(route('admin.cricket.markets'))
            ->assertOk()
            ->assertSee('value="90"', false)
            ->assertSee('value="1.91"', false);

        $this->assertSame('1', Setting::get('cricket_ai_markets_enabled'));
        $this->assertSame('1', Setting::get('cricket_ai_approval_required'));
        $this->assertSame('runs_by_over', Setting::get('cricket_ai_templates'));

        Setting::set('cricket_ai_approval_required', '0');
        Setting::set('cricket_ai_use_model', '1');
        Setting::set('cricket_ai_templates', 'runs_by_over,over_runs,wicket_in_over');
        Setting::flushCache();
        config([
            'services.cricket_ai.key' => 'test-key',
            'services.cricket_ai.url' => 'https://ai.test/v1/chat/completions',
        ]);
        Http::fake([
            'https://ai.test/*' => Http::response(['choices' => [['message' => ['content' => 'not-json']]]]),
        ]);
        $this->board($this->liveRow());

        $summary = app(CricketMarketCycle::class)->run();

        $this->assertSame(3, $summary['generated']);
        $this->assertSame('template', Market::first()->cricketRule->source);
    }

    public function test_wallet_rejects_an_insufficient_balance_and_the_feed_does_not_call_the_provider(): void
    {
        $this->board($this->liveRow());
        app(CricketMarketCycle::class)->run();
        $market = Market::where('name', 'like', 'Will India score 20%')->firstOrFail();
        $no = $market->selections()->where('result_key', 'no')->firstOrFail();
        $player = $this->client();
        $player->forceFill(['balance' => 50])->save();

        try {
            app(BettingService::class)->placeBet($player, $no->id, 100);
            $this->fail('An underfunded bet was accepted.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Insufficient balance', $e->validator->errors()->first());
        }

        $this->assertSame(0, $player->bets()->count());
        $this->assertSame(50.0, (float) $player->fresh()->balance);

        Cache::flush();
        Http::fake();
        $this->getJson(route('cricket.markets.feed'))->assertOk()->assertJsonPath('matches.0.runs', 18);
        Http::assertNothingSent();
    }

    public function test_stale_score_suspends_an_open_market_without_settling_it(): void
    {
        $this->board($this->liveRow());
        app(CricketMarketCycle::class)->run();
        $market = Market::where('name', 'like', 'Will India score 20%')->firstOrFail();

        $row = $this->liveRow(19, 0, '3.5');
        $row['stale'] = true;
        $this->board($row);
        Cache::put('cricket:markets:last-generation', now()->timestamp, 3600);
        app(CricketMarketCycle::class)->run();

        $this->assertSame('suspended', $market->fresh()->status);
        $this->assertSame('open', $market->cricketRule->fresh()->phase);
        $this->assertNull($market->selections()->where('result_key', 'yes')->first()->result);
    }

    public function test_admin_lists_cached_live_matches_without_a_sportsbook_row(): void
    {
        Setting::set('cricket_ai_markets_enabled', '0');
        Setting::set('cricket_ai_approval_required', '1');
        Setting::flushCache();
        Http::fake();

        $sport = Sport::create(['name' => 'Football', 'slug' => 'football', 'icon' => '⚽', 'active' => true]);
        $manual = MatchGame::create([
            'sport_id' => $sport->id,
            'league' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'start_time' => now()->addHour(),
            'status' => 'upcoming',
        ]);
        $manual->markets()->create(['name' => 'Match Winner', 'auto_settle' => true, 'status' => 'open', 'sort_order' => 1]);

        Cache::put('cricbuzz:board', [
            'generated_at' => '2026-11-23T23:30:52+05:30',
            'live' => [
                [
                    'id' => 166687,
                    'label' => 'Tasmania vs Western Australia',
                    'kind' => 'live',
                    'status_group' => 'live',
                    'status' => 'Day 1: Stumps',
                    'status_raw' => 'Day 1: Stumps',
                    'status_label' => 'Stumps',
                    'stale' => false,
                    'innings' => [[
                        'team' => 'TAS',
                        'runs' => 295,
                        'wickets' => 6,
                        'overs' => 94.6,
                    ]],
                    'fetched_at' => now()->toIso8601String(),
                ],
                [
                    'id' => 166687,
                    'label' => 'Tasmania vs Western Australia',
                    'kind' => 'live',
                    'status_group' => 'live',
                    'status_raw' => 'Day 1: Stumps',
                    'status_label' => 'Stumps',
                    'innings' => [[
                        'team' => 'TAS',
                        'runs' => 295,
                        'wickets' => 6,
                        'overs' => 94.6,
                    ]],
                ],
            ],
            'upcoming' => [],
            'results' => [],
            'errors' => [],
            'source' => 'discovery',
        ], 120);

        CricketQuestionCandidate::create([
            'provider' => 'cricbuzz',
            'provider_match_id' => '166687',
            'fingerprint' => 'tas-over-question',
            'template' => 'over_runs',
            'question' => 'Will the 96th over contain 10 or more runs?',
            'parameters' => ['target_over' => 96],
            'source' => 'template',
            'status' => 'pending',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-live',
            'email' => 'admin-live@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cricket.markets'));

        $response->assertOk()
            ->assertSee('Tasmania vs Western Australia')
            ->assertSee('Tasmania')
            ->assertSee('Western Australia')
            ->assertSee('TAS 295/6 (94.6)')
            ->assertSee('Provider 166687')
            ->assertSee('innings 1')
            ->assertSee('Stumps')
            ->assertSee('Will the 96th over contain 10 or more runs?')
            ->assertSee('Global generation is off')
            ->assertDontSee('No live Cricbuzz matches');

        $this->assertSame(1, substr_count($response->getContent(), 'Provider 166687'));
        $this->assertNull(MatchGame::where('provider_match_id', '166687')->first());
        $this->assertSame(0, Market::where('name', 'like', 'Will the 96th%')->count());
        $this->assertSame('upcoming', $manual->fresh()->status);
        $this->assertSame('open', $manual->markets()->first()->status);
        $this->assertSame('0', Setting::get('cricket_ai_markets_enabled'));
        $this->assertSame('1', Setting::get('cricket_ai_approval_required'));
        Http::assertNothingSent();
    }

    public function test_admin_loads_the_board_when_the_cache_is_empty(): void
    {
        Setting::set('cricket_ai_markets_enabled', '0');
        Setting::set('cricket_ai_approval_required', '1');
        Setting::set('cricket_discovery_mode', '1');
        Setting::set('cricket_detail_limit', '6');
        Setting::flushCache();
        Config::set('services.cricbuzz.host', 'cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.key', 'test_rapidapi_key');
        Config::set('services.cricbuzz.base_url', 'https://cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.throttle_ms', 0);
        Config::set('services.cricbuzz.retry_backoff_ms', 0);

        $match = [
            'matchId' => 166687,
            'seriesName' => 'Sheffield Shield',
            'matchFormat' => 'TEST',
            'state' => 'Stumps',
            'status' => 'Day 1: Stumps',
            'team1' => ['teamName' => 'Tasmania', 'teamSName' => 'TAS'],
            'team2' => ['teamName' => 'Western Australia', 'teamSName' => 'WA'],
        ];
        $discovery = [
            'typeMatches' => [[
                'matchType' => 'Domestic',
                'seriesMatches' => [[
                    'seriesAdWrapper' => [
                        'seriesName' => 'Sheffield Shield',
                        'matches' => [['matchInfo' => $match]],
                    ],
                ]],
            ]],
        ];
        $empty = ['typeMatches' => []];

        Http::fake(function ($request) use ($discovery, $empty) {
            $url = $request->url();

            if (str_contains($url, '/matches/v1/live')) {
                return Http::response($discovery, 200);
            }

            if (str_contains($url, '/matches/v1/')) {
                return Http::response($empty, 200);
            }

            if (str_contains($url, '/mcenter/v1/166687/livescore')) {
                return Http::response([
                    'miniscore' => [
                        'custstatus' => 'Day 1: Stumps',
                        'inningsscores' => ['inningsscore' => [[
                            'batteamshortname' => 'TAS',
                            'runs' => 295,
                            'wickets' => 6,
                            'overs' => 94.6,
                        ]]],
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-cold',
            'email' => 'admin-cold@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get(route('admin.cricket.markets'))
            ->assertOk()
            ->assertSee('Tasmania vs Western Australia')
            ->assertSee('TAS 295/6 (94.6)')
            ->assertSee('Provider 166687')
            ->assertDontSee('No live Cricbuzz matches');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/matches/v1/live'));
        $this->assertNull(MatchGame::where('provider_match_id', '166687')->first());
        $this->assertSame('0', Setting::get('cricket_ai_markets_enabled'));
        $this->assertSame('1', Setting::get('cricket_ai_approval_required'));

        $sent = count(Http::recorded());
        $this->actingAs($admin)->get(route('admin.cricket.markets'))
            ->assertOk()
            ->assertSee('Provider 166687');
        $this->assertSame($sent, count(Http::recorded()));
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
            'source' => 'test',
        ], 120);
    }

    /**
     * @return array<string, mixed>
     */
    private function liveRow(int $runs = 18, int $wickets = 0, string $overs = '3.4', int $id = 9001): array
    {
        return [
            'id' => $id,
            'label' => 'India vs Australia',
            'kind' => 'live',
            'status_group' => 'live',
            'status_raw' => 'In Progress',
            'status' => 'In Progress',
            'stale' => false,
            'series' => 'ODI Series',
            'innings' => [[
                'team' => 'India',
                'runs' => $runs,
                'wickets' => $wickets,
                'overs' => $overs,
            ]],
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    private function client(): User
    {
        return User::create([
            'name' => 'Player '.random_int(1000, 9999),
            'username' => 'player'.random_int(1000, 9999),
            'email' => 'player'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 5000,
        ]);
    }
}
