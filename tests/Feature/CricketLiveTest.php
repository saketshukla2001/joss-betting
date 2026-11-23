<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Setting;

class CricketLiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Config::set('services.cricbuzz.host', 'cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.key', 'test_rapidapi_key');
        Config::set('services.cricbuzz.base_url', 'https://cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.throttle_ms', 0);
        Config::set('services.cricbuzz.retry_backoff_ms', 0);
        Config::set('services.cricbuzz.board_ttl', 60);

        Setting::set('cricket_discovery_mode', '0');
        Setting::flushCache();
    }

    /**
     * @return array<string, mixed>
     */
    private function livePayload(string $status): array
    {
        return [
            'miniscore' => [
                'custstatus' => $status,
                'crr' => 8.87,
                'rrr' => 12.86,
                'target' => 182,
                'partnership' => '15(8)',
                'inningsscores' => ['inningsscore' => [
                    ['batteamid' => 243, 'batteamshortname' => 'JHKD', 'runs' => 167, 'wickets' => 6, 'overs' => 18.5],
                    ['batteamid' => 1, 'batteamshortname' => 'UP', 'runs' => 181, 'wickets' => 7, 'overs' => 19.6],
                ]],
                'batsmanstriker' => ['name' => 'Pankaj Kumar', 'runs' => 9, 'balls' => 6],
                'batsmannonstriker' => ['name' => 'Rajandeep Singh', 'runs' => 3, 'balls' => 3],
                'bowlerstriker' => ['name' => 'Bhuvneshwar Kumar', 'overs' => '3.5', 'maidens' => 0, 'runs' => 39, 'wickets' => 2],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scorecardPayload(): array
    {
        return [
            'status' => 'Jharkhand need 15 runs in 7 balls',
            'ismatchcomplete' => false,
            'scorecard' => [
                [
                    'inningsid' => 1,
                    'batteamname' => 'Uttar Pradesh',
                    'score' => 181,
                    'wickets' => 7,
                    'overs' => 19.6,
                    'runrate' => 9.1,
                    'batsman' => [
                        ['name' => 'Divyansh joshi', 'runs' => 7, 'balls' => 9, 'fours' => 1, 'sixes' => 0, 'strkrate' => '77.78', 'outdec' => 'c Harsh Raj b Anukul Roy'],
                    ],
                    'bowler' => [
                        ['name' => 'Anukul Roy', 'overs' => '4', 'maidens' => 0, 'runs' => 30, 'wickets' => 2, 'economy' => '7.5'],
                    ],
                    'extras' => ['runs' => 5],
                ],
            ],
        ];
    }

    private function fakeApi(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/miniscorecard')) {
                return Http::response($this->livePayload('Jharkhand need 15 runs in 7 balls'), 200);
            }

            if (str_contains($url, '/scard')) {
                return Http::response($this->scorecardPayload(), 200);
            }

            $status = str_contains($url, '165080')
                ? 'Odisha won by 13 runs'
                : 'Jharkhand need 15 runs in 7 balls';

            return Http::response($this->livePayload($status), 200);
        });
    }

    public function test_board_endpoint_groups_live_and_finished_matches(): void
    {
        $this->fakeApi();

        $response = $this->getJson('/cricket/board')->assertOk();

        $response->assertJsonStructure([
            'generated_at',
            'live' => [['id', 'label', 'url', 'kind', 'innings', 'status']],
            'upcoming',
            'results',
            'errors',
        ]);

        $this->assertCount(5, $response->json('live'));
        $this->assertCount(1, $response->json('results'));
        $this->assertSame('result', $response->json('results.0.kind'));
        $this->assertStringContainsString('/cricket/165080', $response->json('results.0.url'));
        $this->assertSame(0, $response->json('errors') ? count($response->json('errors')) : 0);
    }

    public function test_cricket_page_renders_board_shell_without_api_calls(): void
    {
        $this->fakeApi();

        $this->get('/cricket')
            ->assertOk()
            ->assertSee('data-cricket-board', false)
            ->assertSee(route('cricket.board'), false)
            ->assertSee('Live cricket');

        Http::assertNothingSent();
    }

    public function test_home_page_includes_cricket_section_without_api_calls(): void
    {
        $this->fakeApi();

        $this->get('/')
            ->assertOk()
            ->assertSee('data-cricket-board', false)
            ->assertSee('Live cricket');

        Http::assertNothingSent();
    }

    public function test_detail_page_renders_scorecard(): void
    {
        $this->fakeApi();

        $this->get('/cricket/165061')
            ->assertOk()
            ->assertSee('Match score')
            ->assertSee('Pankaj Kumar')
            ->assertSee('Bhuvneshwar Kumar')
            ->assertSee('Uttar Pradesh')
            ->assertSee('Divyansh joshi')
            ->assertSee('Anukul Roy');
    }

    public function test_hidden_match_detail_is_not_publicly_reachable(): void
    {
        Setting::set('cricket_hidden_match_ids', '[161285]');
        Setting::flushCache();

        $this->get('/cricket/161285')->assertNotFound();
    }

    public function test_unknown_match_returns_404(): void
    {
        $this->get('/cricket/999999')->assertNotFound();
    }

    public function test_board_is_cached_between_requests(): void
    {
        $this->fakeApi();

        $this->getJson('/cricket/board')->assertOk();
        $this->getJson('/cricket/board')->assertOk();

        // Six tracked matches, fetched once each.
        Http::assertSentCount(6);
    }

    public function test_forced_refresh_is_rate_limited(): void
    {
        $this->fakeApi();

        $first = $this->getJson('/cricket/board?refresh=1')->assertOk();
        $second = $this->getJson('/cricket/board?refresh=1')->assertOk();

        $this->assertFalse($first->json('refresh_limited'));
        $this->assertTrue($second->json('refresh_limited'));
    }
}
