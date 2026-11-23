<?php

namespace Tests\Feature;

use App\Models\MatchGame;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CricketLiveOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::flushCache();
        Setting::set('cricket_discovery_mode', '0');
        Setting::set('live_only_sport', 'cricket');
        Setting::flushCache();
    }

    private function sport(string $name, string $slug, int $order): Sport
    {
        return Sport::create([
            'name' => $name,
            'slug' => $slug,
            'icon' => '🏏',
            'active' => true,
            'sort_order' => $order,
        ]);
    }

    private function liveMatch(Sport $sport, string $home, string $away, int $hoursAgo = 0): MatchGame
    {
        return MatchGame::create([
            'sport_id' => $sport->id,
            'league' => $sport->name.' League',
            'home_team' => $home,
            'away_team' => $away,
            'start_time' => now()->subHours($hoursAgo),
            'status' => 'live',
        ]);
    }

    private function client(): User
    {
        return User::create([
            'name' => 'Viewer',
            'username' => 'viewer'.random_int(1000, 9999),
            'email' => 'viewer'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => 1000,
        ]);
    }

    public function test_home_live_section_shows_only_cricket_when_configured(): void
    {
        $cricket = $this->sport('Cricket', 'cricket', 1);
        $football = $this->sport('Football', 'football', 2);

        $this->liveMatch($cricket, 'India', 'Australia');
        $this->liveMatch($football, 'Arsenal', 'Chelsea');

        $html = $this->get('/')->assertOk()->getContent();
        $liveSection = Str::between($html, 'Live right now', 'Featured matches');

        $this->assertStringContainsString('India', $liveSection);
        $this->assertStringNotContainsString('Arsenal', $liveSection);
    }

    public function test_all_sports_setting_restores_every_sport_in_live(): void
    {
        Setting::set('live_only_sport', 'all');
        Setting::flushCache();

        $cricket = $this->sport('Cricket', 'cricket', 1);
        $football = $this->sport('Football', 'football', 2);

        $this->liveMatch($cricket, 'India', 'Australia');
        $this->liveMatch($football, 'Arsenal', 'Chelsea');

        $html = $this->get('/')->assertOk()->getContent();
        $liveSection = Str::between($html, 'Live right now', 'Featured matches');

        $this->assertStringContainsString('India', $liveSection);
        $this->assertStringContainsString('Arsenal', $liveSection);
    }

    public function test_live_tab_filters_to_cricket_and_includes_the_cricket_board(): void
    {
        $cricket = $this->sport('Cricket', 'cricket', 1);
        $football = $this->sport('Football', 'football', 2);

        $this->liveMatch($cricket, 'India', 'Australia');
        $this->liveMatch($football, 'Arsenal', 'Chelsea');

        $this->get('/sports?status=live')
            ->assertOk()
            ->assertSee('data-cricket-board', false)
            ->assertSee('India')
            ->assertDontSee('Arsenal')
            ->assertDontSee('0 events available');
    }

    public function test_client_dashboard_live_strip_filters_to_cricket(): void
    {
        $cricket = $this->sport('Cricket', 'cricket', 1);
        $football = $this->sport('Football', 'football', 2);

        $this->liveMatch($cricket, 'India', 'Australia');
        $this->liveMatch($football, 'Arsenal', 'Chelsea');

        $this->actingAs($this->client())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('India')
            ->assertDontSee('Arsenal');
    }

    public function test_hero_does_not_fall_back_to_other_sports_in_cricket_only_mode(): void
    {
        $cricket = $this->sport('Cricket', 'cricket', 1);
        $football = $this->sport('Football', 'football', 2);

        // No live cricket, but a featured football match exists.
        MatchGame::create([
            'sport_id' => $football->id,
            'league' => 'Premier League',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'start_time' => now()->addHours(2),
            'status' => 'upcoming',
            'featured' => true,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $hero = Str::between($html, '<section class="hero">', '</section>');

        $this->assertStringNotContainsString('Arsenal', $hero);
        $this->assertStringContainsString('Markets open shortly', $hero);
    }

    public function test_hero_does_not_show_stale_live_matches(): void
    {
        $cricket = $this->sport('Cricket', 'cricket', 1);

        MatchGame::create([
            'sport_id' => $cricket->id,
            'league' => 'Old League',
            'home_team' => 'OldHome',
            'away_team' => 'OldAway',
            'start_time' => now()->subHours(12),
            'status' => 'live',
            'featured' => true,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $hero = Str::between($html, '<section class="hero">', '</section>');

        $this->assertStringNotContainsString('OldHome', $hero);
    }

    public function test_feed_disabled_setting_hides_the_board_and_returns_a_disabled_payload(): void
    {
        Setting::set('cricket_feed_enabled', '0');
        Setting::flushCache();

        $this->get('/')->assertOk()->assertDontSee('data-cricket-board', false);
        $this->get('/sports?status=live')->assertOk()->assertDontSee('data-cricket-board', false);
        $this->get('/cricket')->assertOk()->assertSee('disabled by the administrator')->assertDontSee('data-cricket-board', false);

        $response = $this->getJson('/cricket/board')->assertOk();
        $this->assertTrue($response->json('disabled'));
        $this->assertCount(0, $response->json('live'));
    }

    public function test_stale_database_live_matches_are_hidden_and_restored_by_threshold(): void
    {
        $cricket = $this->sport('Cricket', 'cricket', 1);
        $this->liveMatch($cricket, 'Old', 'Fixture', 10);

        $html = $this->get('/')->assertOk()->getContent();
        $liveSection = Str::between($html, 'Live right now', 'Featured matches');
        $this->assertStringNotContainsString('Old', $liveSection);

        Setting::set('live_stale_hours', '0');
        Setting::flushCache();

        $html = $this->get('/')->assertOk()->getContent();
        $liveSection = Str::between($html, 'Live right now', 'Featured matches');
        $this->assertStringContainsString('Old', $liveSection);
    }
}
