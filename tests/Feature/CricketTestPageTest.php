<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\User;

class CricketTestPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Config::set('services.sportmonks.cricket_key', 'test_sportmonks_token');
        Config::set('services.sportmonks.cricket_base_url', 'https://cricket.sportmonks.com/api/v2.0');

        $this->actingAs(User::create([
            'name' => 'Admin',
            'username' => 'admin'.random_int(1000, 9999),
            'email' => 'admin'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function fixturePayload(int $id = 123, string $status = 'NS'): array
    {
        return [
            'resource' => 'fixtures',
            'id' => $id,
            'league_id' => 3,
            'season_id' => 6,
            'stage_id' => 1755,
            'round' => '2nd T20I',
            'localteam_id' => 40,
            'visitorteam_id' => 41,
            'starting_at' => '2026-11-24T08:15:00.000000Z',
            'type' => 'T20I',
            'live' => $status !== 'NS',
            'status' => $status,
            'note' => '',
            'venue_id' => 96,
            'total_overs_played' => null,
            'localteam' => ['data' => [
                'id' => 40, 'name' => 'South Africa', 'code' => 'SA',
                'image_path' => 'https://cdn.sportmonks.com/images/cricket/teams/4/36.png',
            ]],
            'visitorteam' => ['data' => [
                'id' => 41, 'name' => 'India', 'code' => 'IND',
                'image_path' => 'https://cdn.sportmonks.com/images/cricket/teams/4/38.png',
            ]],
            'venue' => ['data' => ['id' => 96, 'name' => 'Kingsmead', 'city' => 'Durban']],
            'league' => ['data' => ['id' => 3, 'name' => 'Twenty20 International', 'code' => 'T20I']],
            'season' => ['data' => ['id' => 6, 'name' => '2026']],
            'stage' => ['data' => ['id' => 1755, 'name' => 'Group Stage']],
            'runs' => [],
        ];
    }

    private function fakeFeed(string $fixturesStatus = 'NS'): void
    {
        Http::fake([
            'https://cricket.sportmonks.com/api/v2.0/livescores*' => Http::response([
                'data' => [$this->fixturePayload(900, '1st Innings')],
            ], 200),
            'https://cricket.sportmonks.com/api/v2.0/fixtures*' => Http::response([
                'data' => [$this->fixturePayload(123, $fixturesStatus)],
            ], 200),
        ]);
    }

    public function test_cricket_test_page_renders_live_and_upcoming_sections(): void
    {
        $this->fakeFeed();

        $this->get('/cricket-test')
            ->assertOk()
            ->assertSee('Live Matches')
            ->assertSee('Upcoming Matches')
            ->assertSee('Recent Results')
            ->assertSee('South Africa')
            ->assertSee('India')
            ->assertSee('Twenty20 International')
            ->assertSee('Kingsmead')
            ->assertSee('https://cdn.sportmonks.com/images/cricket/teams/4/36.png', false)
            ->assertSee('Upcoming')
            ->assertDontSee('test_sportmonks_token');
    }

    public function test_upcoming_request_uses_the_ns_status_filter(): void
    {
        $this->fakeFeed();

        $this->get('/cricket-test')->assertOk();

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, 'fixtures')
                && (str_contains($url, 'status%5D=NS') || str_contains($url, 'status]=NS'));
        });
    }

    public function test_cricket_test_page_reports_a_rejected_token_without_crashing(): void
    {
        Http::fake([
            'https://cricket.sportmonks.com/api/v2.0/*' => Http::response([
                'message' => 'Unauthenticated',
            ], 401),
        ]);

        $this->get('/cricket-test')
            ->assertOk()
            ->assertSee('Sportmonks rejected the API token')
            ->assertSee('unauthenticated');
    }

    public function test_cricket_test_detail_page_renders_fixture_and_raw_payload(): void
    {
        Http::fake([
            'https://cricket.sportmonks.com/api/v2.0/fixtures/123*' => Http::response([
                'data' => $this->fixturePayload(123, 'Finished'),
            ], 200),
        ]);

        $this->get('/cricket-test/123')
            ->assertOk()
            ->assertSee('South Africa vs India')
            ->assertSee('Kingsmead')
            ->assertSee('Raw payload');
    }

    public function test_successful_responses_are_cached_between_page_loads(): void
    {
        $this->fakeFeed();

        $this->get('/cricket-test')->assertOk();
        $this->get('/cricket-test')->assertOk();

        // livescores + upcoming fixtures + recent fixtures, fetched once each.
        Http::assertSentCount(3);
    }
}
