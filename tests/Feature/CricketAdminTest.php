<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CricketAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::flushCache();

        Config::set('services.cricbuzz.host', 'cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.key', 'test_rapidapi_key');
        Config::set('services.cricbuzz.base_url', 'https://cricbuzz-cricket.p.rapidapi.com');
        Config::set('services.cricbuzz.throttle_ms', 0);
        Config::set('services.cricbuzz.retry_backoff_ms', 0);

        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, '/matches/v1/')) {
                return Http::response([
                    'typeMatches' => [[
                        'matchType' => 'International',
                        'seriesMatches' => [[
                            'seriesAdWrapper' => [
                                'seriesName' => 'Test Series',
                                'matches' => [[
                                    'matchInfo' => [
                                        'matchId' => 9001,
                                        'seriesName' => 'Test Series',
                                        'matchDesc' => '1st T20I',
                                        'matchFormat' => 'T20',
                                        'state' => '1st Innings',
                                        'status' => 'India need 60 runs',
                                        'team1' => ['teamName' => 'India', 'teamSName' => 'IND'],
                                        'team2' => ['teamName' => 'Australia', 'teamSName' => 'AUS'],
                                        'venueInfo' => ['ground' => 'Ground', 'city' => 'City'],
                                    ],
                                ]],
                            ],
                        ]],
                    ]],
                ], 200);
            }

            return Http::response([
                'miniscore' => [
                    'custstatus' => 'India need 60 runs',
                    'crr' => 8.2,
                    'inningsscores' => ['inningsscore' => [
                        ['batteamid' => 1, 'batteamshortname' => 'IND', 'runs' => 120, 'wickets' => 3, 'overs' => 14.2],
                    ]],
                ],
            ], 200);
        });
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'username' => 'admin'.random_int(1000, 9999),
            'email' => 'admin'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function client(): User
    {
        return User::create([
            'name' => 'Client',
            'username' => 'client'.random_int(1000, 9999),
            'email' => 'client'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 100,
        ]);
    }

    public function test_cricket_admin_pages_require_admin_authorization(): void
    {
        $this->get('/admin/cricket')->assertRedirect('/login');

        $this->actingAs($this->client())->get('/admin/cricket')->assertForbidden();

        $this->actingAs($this->admin())->get('/admin/cricket')->assertOk()->assertSee('Cricket feed');
    }

    public function test_cricket_admin_sections_render(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/cricket/matches')->assertOk()->assertSee('Provider matches');
        $this->actingAs($admin)->get('/admin/cricket/health')->assertOk()->assertSee('API health');
        $this->actingAs($admin)->get('/admin/cricket/settings')->assertOk()->assertSee('Cricket configuration');
    }

    public function test_admin_can_hide_and_show_a_provider_match(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/cricket/matches/9001/visibility')->assertRedirect();
        $hidden = json_decode((string) Setting::get('cricket_hidden_match_ids'), true) ?: [];
        $this->assertContains(9001, $hidden);

        $this->actingAs($admin)->post('/admin/cricket/matches/9001/visibility')->assertRedirect();
        $hidden = json_decode((string) Setting::get('cricket_hidden_match_ids'), true) ?: [];
        $this->assertNotContains(9001, $hidden);
    }

    public function test_admin_refresh_is_rate_limited(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/cricket/matches/9001/refresh')->assertRedirect()->assertSessionHas('success');

        $this->actingAs($admin)->post('/admin/cricket/matches/9001/refresh')->assertRedirect()->assertSessionHas('error');
    }

    public function test_admin_can_update_cricket_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/cricket/settings', [
            'live_only_sport' => 'all',
            'cricket_feed_enabled' => '1',
            'cricket_board_enabled' => '0',
            'cricket_discovery_mode' => '0',
            'cricket_fallback_ids' => "165061|UP vs Jharkhand\n165080|Odisha vs Goa",
            'cricket_stale_minutes' => 20,
            'cricket_detail_limit' => 3,
            'cricket_discovery_ttl' => 90,
            'live_stale_hours' => 12,
        ])->assertRedirect()->assertSessionHas('success');

        Setting::flushCache();

        $this->assertSame('all', Setting::get('live_only_sport'));
        $this->assertSame('0', Setting::get('cricket_board_enabled'));
        $this->assertSame('0', Setting::get('cricket_discovery_mode'));
        $this->assertSame('20', Setting::get('cricket_stale_minutes'));
        $this->assertSame('3', Setting::get('cricket_detail_limit'));
        $this->assertSame('12', Setting::get('live_stale_hours'));
        $this->assertStringContainsString('165061|UP vs Jharkhand', (string) Setting::get('cricket_fallback_ids'));
    }
}
