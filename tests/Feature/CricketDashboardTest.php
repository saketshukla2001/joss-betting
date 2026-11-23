<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Setting;

class CricketDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Config::set('services.open_cricket.key', 'oc_live_test_key');
        Config::set('services.open_cricket.base_url', 'https://opencricketapi.com/v1/');

        Setting::set('legacy_opencricket_enabled', '1');
        Setting::flushCache();
    }

    private function client(): User
    {
        return User::create([
            'name' => 'Cricket Viewer',
            'username' => 'viewer'.random_int(1000, 9999),
            'email' => 'viewer'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 1000,
        ]);
    }

    private function fakeMatches(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/matches*' => Http::response([
                'data' => [
                    [
                        'id' => 'match_cs_test1',
                        'date' => '2026-10-07',
                        'competition' => ['id' => 'comp_test', 'name' => 'West Indies Women tour of Zimbabwe'],
                        'stage' => 'Match 3',
                        'format' => 'T20',
                        'teams' => [
                            ['id' => 'team_zim', 'name' => 'Zimbabwe'],
                            ['id' => 'team_wi', 'name' => 'West Indies'],
                        ],
                        'innings' => [
                            ['number' => 1, 'team_id' => 'team_zim', 'runs' => 142, 'wickets' => 6, 'overs' => '20'],
                            ['number' => 2, 'team_id' => 'team_wi', 'runs' => 143, 'wickets' => 2, 'overs' => '17'],
                        ],
                        'result' => 'West Indies won by 8 wickets',
                        'winner_id' => 'team_wi',
                    ],
                ],
                'meta' => [
                    'source' => 'Cricsheet',
                    'plan' => 'starter',
                    'count' => 1,
                ],
            ], 200),
        ]);
    }

    public function test_legacy_feed_is_disabled_by_default_without_api_calls(): void
    {
        Setting::set('legacy_opencricket_enabled', '0');
        Setting::flushCache();
        Http::fake();

        $this->actingAs($this->client())
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Cricket · API feed')
            ->assertDontSee('Cricket feed unavailable');

        Http::assertNothingSent();
    }

    public function test_dashboard_renders_cricket_matches_from_the_api(): void
    {
        $this->fakeMatches();

        $this->actingAs($this->client())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Cricket · API feed')
            ->assertSee('Zimbabwe vs West Indies')
            ->assertSee('142/6')
            ->assertSee('143/2')
            ->assertSee('West Indies won by 8 wickets')
            ->assertSee('Cricsheet')
            ->assertDontSee('oc_live_test_key');

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer oc_live_test_key'));
    }

    public function test_dashboard_still_loads_when_the_api_fails(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/*' => Http::response([
                'error' => ['code' => 'invalid_api_key', 'message' => 'Invalid API key'],
            ], 401),
        ]);

        $this->actingAs($this->client())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Cricket feed unavailable')
            ->assertDontSee('Cricket · API feed');
    }

    public function test_successful_feed_is_cached_between_requests(): void
    {
        $this->fakeMatches();

        $user = $this->client();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/dashboard')->assertOk();

        Http::assertSentCount(1);
    }
}
