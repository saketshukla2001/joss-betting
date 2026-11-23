<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CricketDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::flushCache();
        Setting::set('cricket_discovery_mode', '1');
        Setting::set('cricket_board_enabled', '1');
        Setting::set('cricket_detail_limit', '6');
        Setting::flushCache();

        Config::set('services.cricbuzz.host', 'cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.key', 'test_rapidapi_key');
        Config::set('services.cricbuzz.base_url', 'https://cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.throttle_ms', 0);
        Config::set('services.cricbuzz.retry_backoff_ms', 0);
    }

    /**
     * @param  array<int, array<string, mixed>>  $matches
     * @return array<string, mixed>
     */
    private function discoveryPayload(array $matches): array
    {
        return [
            'typeMatches' => [[
                'matchType' => 'International',
                'seriesMatches' => [[
                    'seriesAdWrapper' => [
                        'seriesName' => 'Test Series',
                        'matches' => array_map(fn ($match) => ['matchInfo' => $match], $matches),
                    ],
                ]],
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function matchInfo(int $id, string $one, string $two, string $status): array
    {
        return [
            'matchId' => $id,
            'seriesName' => 'Test Series',
            'matchDesc' => '1st T20I',
            'matchFormat' => 'T20',
            'startDate' => 1795400000000,
            'state' => $status,
            'status' => $status,
            'team1' => ['teamName' => $one, 'teamSName' => strtoupper(substr($one, 0, 3))],
            'team2' => ['teamName' => $two, 'teamSName' => strtoupper(substr($two, 0, 3))],
            'venueInfo' => ['ground' => 'Test Ground', 'city' => 'Test City'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function livePayload(string $status): array
    {
        return [
            'miniscore' => [
                'custstatus' => $status,
                'crr' => 8.2,
                'rrr' => 10.1,
                'target' => 180,
                'partnership' => '10(6)',
                'inningsscores' => ['inningsscore' => [
                    ['batteamid' => 1, 'batteamshortname' => 'IND', 'runs' => 120, 'wickets' => 3, 'overs' => 14.2],
                    ['batteamid' => 2, 'batteamshortname' => 'AUS', 'runs' => 179, 'wickets' => 8, 'overs' => 20],
                ]],
                'batsmanstriker' => ['name' => 'Striker', 'runs' => 40, 'balls' => 25],
                'batsmannonstriker' => ['name' => 'Partner', 'runs' => 12, 'balls' => 10],
                'bowlerstriker' => ['name' => 'Bowler', 'overs' => '3.2', 'maidens' => 0, 'runs' => 25, 'wickets' => 1],
            ],
        ];
    }

    private function fakeDiscovery(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/matches/v1/live')) {
                return Http::response($this->discoveryPayload([
                    $this->matchInfo(9001, 'India', 'Australia', '1st Innings'),
                    $this->matchInfo(9002, 'Delhi', 'Mumbai', 'Aban.'),
                ]), 200);
            }

            if (str_contains($url, '/matches/v1/upcoming')) {
                return Http::response($this->discoveryPayload([
                    $this->matchInfo(9003, 'England', 'India', 'NS'),
                ]), 200);
            }

            if (str_contains($url, '/matches/v1/recent')) {
                return Http::response($this->discoveryPayload([
                    $this->matchInfo(9004, 'Pakistan', 'India', 'Pakistan won by 5 wickets'),
                    $this->matchInfo(9001, 'India', 'Australia', 'India won by 4 runs'),
                ]), 200);
            }

            if (str_contains($url, '/mcenter/v1/9001/livescore')) {
                return Http::response($this->livePayload('India need 60 runs in 34 balls'), 200);
            }

            if (str_contains($url, '/mcenter/v1/9001/miniscorecard')) {
                return Http::response($this->livePayload('India need 60 runs in 34 balls'), 200);
            }

            if (str_contains($url, '/mcenter/v1/9001/scard')) {
                return Http::response([
                    'status' => 'India need 60 runs in 34 balls',
                    'ismatchcomplete' => false,
                    'scorecard' => [[
                        'inningsid' => 1,
                        'batteamname' => 'Australia',
                        'score' => 179,
                        'wickets' => 8,
                        'overs' => 20,
                        'runrate' => 8.95,
                        'batsman' => [['name' => 'Batter One', 'runs' => 55, 'balls' => 40, 'fours' => 4, 'sixes' => 2, 'strkrate' => '137.5', 'outdec' => 'c & b Bowler']],
                        'bowler' => [['name' => 'Bowler One', 'overs' => '4', 'maidens' => 0, 'runs' => 30, 'wickets' => 2, 'economy' => '7.5']],
                    ]],
                ], 200);
            }

            return Http::response(['message' => 'not found'], 404);
        });
    }

    public function test_discovery_builds_normalized_board_and_deduplicates_matches(): void
    {
        $this->fakeDiscovery();

        $response = $this->getJson('/cricket/board')->assertOk();

        $this->assertCount(1, $response->json('live'));
        $this->assertCount(1, $response->json('upcoming'));
        $this->assertCount(2, $response->json('results'));

        $live = $response->json('live.0');
        $this->assertSame(9001, $live['id']);
        $this->assertSame('Live', $live['status_label']);
        $this->assertSame('India need 60 runs in 34 balls', $live['status_raw']);
        $this->assertNotEmpty($live['innings']);
        $this->assertSame('discovery', $response->json('source'));

        $abandoned = collect($response->json('results'))->firstWhere('id', 9002);
        $this->assertSame('Abandoned', $abandoned['status_label']);

        $ids = collect($response->json('live'))->merge($response->json('upcoming'))->merge($response->json('results'))->pluck('id');
        $this->assertSame($ids->count(), $ids->unique()->count(), 'provider matches must not be duplicated');
    }

    public function test_discovery_calls_are_cached_between_board_requests(): void
    {
        $this->fakeDiscovery();

        $this->getJson('/cricket/board')->assertOk();
        $this->getJson('/cricket/board')->assertOk();

        // 3 discovery calls + 1 livescore detail for the only live match.
        Http::assertSentCount(4);
    }

    public function test_hidden_match_is_not_exposed_on_the_board(): void
    {
        Setting::set('cricket_hidden_match_ids', '[9003]');
        Setting::flushCache();
        $this->fakeDiscovery();

        $response = $this->getJson('/cricket/board')->assertOk();

        $this->assertCount(0, $response->json('upcoming'));
    }

    public function test_board_falls_back_to_configured_ids_when_discovery_fails(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/matches/v1/')) {
                return Http::response(['message' => 'rate limited'], 429);
            }

            if (str_contains($url, '/mcenter/v1/165061/livescore')) {
                return Http::response($this->livePayload('Jharkhand need 15 runs in 7 balls'), 200);
            }

            return Http::response(['message' => 'rate limited'], 429);
        });

        $response = $this->getJson('/cricket/board')->assertOk();

        $this->assertSame('fallback', $response->json('source'));
        $this->assertNotEmpty($response->json('live'));
        $this->assertSame('UP vs Jharkhand', $response->json('live.0.label'));
    }

    public function test_discovered_match_detail_page_is_available(): void
    {
        $this->fakeDiscovery();

        $this->getJson('/cricket/board')->assertOk();

        $this->get('/cricket/9001')
            ->assertOk()
            ->assertSee('Batter One')
            ->assertSee('Bowler One');
    }
}
