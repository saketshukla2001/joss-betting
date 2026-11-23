<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\User;

class CricbuzzTestPageTest extends TestCase
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
    private function livescorePayload(): array
    {
        return [
            'miniscore' => [
                'custstatus' => 'Jharkhand need 15 runs in 7 balls',
                'crr' => 8.87,
                'rrr' => 12.86,
                'target' => 182,
                'partnership' => '15(8)',
                'lastwkt' => 'Md Kounain Quraishi c Adarsh Singh b Shivam Mavi 52(30)',
                'inningsscores' => ['inningsscore' => [
                    ['batteamid' => 243, 'batteamshortname' => 'JHKD', 'runs' => 167, 'wickets' => 6, 'overs' => 18.5],
                    ['batteamid' => 1, 'batteamshortname' => 'UP', 'runs' => 181, 'wickets' => 7, 'overs' => 19.6],
                ]],
                'batsmanstriker' => ['name' => 'Pankaj Kumar', 'runs' => 9, 'balls' => 6, 'fours' => 1, 'sixes' => 0, 'strkrate' => '150.00'],
                'batsmannonstriker' => ['name' => 'Rajandeep Singh', 'runs' => 3, 'balls' => 3, 'fours' => 0, 'sixes' => 0, 'strkrate' => '100.00'],
                'bowlerstriker' => ['name' => 'Bhuvneshwar Kumar', 'overs' => '3.5', 'maidens' => 0, 'runs' => 39, 'wickets' => 2, 'economy' => '10.17'],
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
                    'batteamsname' => 'UP',
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
                    'extras' => ['runs' => 5, 'wides' => 3],
                ],
            ],
        ];
    }

    private function fakeApi(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/miniscorecard')) {
                return Http::response($this->livescorePayload(), 200);
            }

            if (str_contains($url, '/scard')) {
                return Http::response($this->scorecardPayload(), 200);
            }

            return Http::response($this->livescorePayload(), 200);
        });
    }

    public function test_cricbuzz_page_renders_all_six_matches(): void
    {
        $this->fakeApi();

        $this->get('/cricbuzz-test')
            ->assertOk()
            ->assertSee('UP vs Jharkhand')
            ->assertSee('New Zealand vs India')
            ->assertSee('JHKD')
            ->assertSee('167/6')
            ->assertSee('Jharkhand need 15 runs in 7 balls')
            ->assertSee('8.87')
            ->assertDontSee('test_rapidapi_key');
    }

    public function test_cricbuzz_detail_renders_live_panel_and_scorecard(): void
    {
        $this->fakeApi();

        $this->get('/cricbuzz-test/165061')
            ->assertOk()
            ->assertSee('At the crease')
            ->assertSee('Pankaj Kumar')
            ->assertSee('Bhuvneshwar Kumar')
            ->assertSee('Uttar Pradesh')
            ->assertSee('Divyansh joshi')
            ->assertSee('Anukul Roy');
    }

    public function test_cricbuzz_page_handles_rate_limit_errors(): void
    {
        Http::fake([
            'https://cricbuzz-cricket.p.rapidapi.com/*' => Http::response([
                'message' => 'You have exceeded the rate limit per second for your plan, BASIC',
            ], 429),
        ]);

        $this->get('/cricbuzz-test')
            ->assertOk()
            ->assertSee('rate limit');
    }

    public function test_standalone_results_screen_renders(): void
    {
        $this->fakeApi();

        $this->get('/cricbuzz-results')
            ->assertOk()
            ->assertSee('Cricbuzz live scores')
            ->assertSee('UP vs Jharkhand')
            ->assertSee('New Zealand vs India')
            ->assertSee('JHKD')
            ->assertSee('Scorecard')
            ->assertDontSee('test_rapidapi_key');

        $this->assertStringStartsWith('<!DOCTYPE html>', $this->get('/cricbuzz-results')->getContent());
    }

    public function test_cricbuzz_list_is_cached_between_requests(): void
    {
        $this->fakeApi();

        $this->get('/cricbuzz-test')->assertOk();
        $this->get('/cricbuzz-test')->assertOk();

        // Six livescore calls for the six fixed match IDs, fetched once.
        Http::assertSentCount(6);
    }
}
