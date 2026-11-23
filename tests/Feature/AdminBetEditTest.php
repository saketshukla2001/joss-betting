<?php

namespace Tests\Feature;

use App\Models\Bet;
use App\Models\Market;
use App\Models\MatchGame;
use App\Models\Selection;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBetEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::flushCache();
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'username' => 'admin'.random_int(1000, 9999),
            'email' => 'admin'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function agent(): User
    {
        return User::create([
            'name' => 'Test Agent',
            'username' => 'agent'.random_int(1000, 9999),
            'email' => 'agent'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'agent',
            'status' => 'active',
            'balance' => 0,
        ]);
    }

    private function client(float $balance = 5000, ?User $parent = null): User
    {
        return User::create([
            'name' => 'Test Player',
            'username' => 'player'.random_int(1000, 9999),
            'email' => 'player'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => $balance,
            'parent_id' => $parent?->id,
        ]);
    }

    /**
     * @return array{0: MatchGame, 1: Market, 2: Selection, 3: Selection}
     */
    private function fixture(float $homeOdds = 2.00, float $awayOdds = 3.00): array
    {
        $sport = Sport::firstOrCreate(
            ['slug' => 'football'],
            ['name' => 'Football', 'icon' => '⚽', 'active' => true]
        );

        $match = MatchGame::create([
            'sport_id' => $sport->id,
            'league' => 'Test League',
            'home_team' => 'Home FC',
            'away_team' => 'Away FC',
            'start_time' => now()->addHours(2),
            'status' => 'upcoming',
        ]);

        $market = Market::create([
            'match_id' => $match->id,
            'name' => 'Match Winner',
            'auto_settle' => true,
            'status' => 'open',
        ]);

        $home = Selection::create([
            'market_id' => $market->id,
            'name' => 'Home FC',
            'result_key' => 'home',
            'odds' => $homeOdds,
            'status' => 'active',
        ]);

        $away = Selection::create([
            'market_id' => $market->id,
            'name' => 'Away FC',
            'result_key' => 'away',
            'odds' => $awayOdds,
            'status' => 'active',
        ]);

        return [$match, $market, $home, $away];
    }

    private function placeBet(User $user, float $stake, ?Selection $selection = null, float $odds = 2.00): Bet
    {
        [, , $home] = $this->fixture(2.00, $odds);
        $selection ??= $home;

        $this->actingAs($user)->postJson('/bets', [
            'selection_id' => $selection->id,
            'stake' => $stake,
        ])->assertOk();

        return Bet::latest('id')->firstOrFail();
    }

    public function test_admin_can_raise_stake_and_odds(): void
    {
        $admin = $this->admin();
        $user = $this->client(5000);
        $bet = $this->placeBet($user, 500);

        $this->actingAs($admin)
            ->put("/admin/bets/{$bet->id}", ['stake' => 800, 'odds' => 2.50])
            ->assertRedirect(route('admin.bets.show', $bet));

        $bet->refresh();

        $this->assertSame(800.0, (float) $bet->stake);
        $this->assertSame(2.5, (float) $bet->odds);
        $this->assertSame(2000.0, (float) $bet->potential_return);
        $this->assertSame('pending', $bet->status);

        $this->assertSame(4200.0, (float) $user->fresh()->balance);
        $this->assertSame(800.0, (float) $user->fresh()->exposure);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'bet',
            'amount' => -300,
            'reference_type' => 'bet',
            'reference_id' => $bet->id,
        ]);

        $this->assertDatabaseHas('operation_logs', ['operation' => 'BET_EDIT']);
    }

    public function test_admin_can_lower_stake_and_player_is_refunded(): void
    {
        $admin = $this->admin();
        $user = $this->client(5000);
        $bet = $this->placeBet($user, 500);

        $this->actingAs($admin)
            ->put("/admin/bets/{$bet->id}", ['stake' => 200, 'odds' => 2.00])
            ->assertRedirect(route('admin.bets.show', $bet));

        $bet->refresh();

        $this->assertSame(200.0, (float) $bet->stake);
        $this->assertSame(400.0, (float) $bet->potential_return);
        $this->assertSame(4800.0, (float) $user->fresh()->balance);
        $this->assertSame(200.0, (float) $user->fresh()->exposure);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'refund',
            'amount' => 300,
            'reference_id' => $bet->id,
        ]);
    }

    public function test_raise_is_rejected_when_player_balance_is_too_low(): void
    {
        $admin = $this->admin();
        $user = $this->client(1000);
        $bet = $this->placeBet($user, 900); // balance left: 100

        $this->actingAs($admin)
            ->from(route('admin.bets.show', $bet))
            ->put("/admin/bets/{$bet->id}", ['stake' => 2000, 'odds' => 2.00])
            ->assertRedirect(route('admin.bets.show', $bet))
            ->assertSessionHasErrors('stake');

        $bet->refresh();

        $this->assertSame(900.0, (float) $bet->stake);
        $this->assertSame(100.0, (float) $user->fresh()->balance);
        $this->assertSame(900.0, (float) $user->fresh()->exposure);
    }

    public function test_settled_ticket_cannot_be_edited(): void
    {
        $admin = $this->admin();
        $user = $this->client(5000);
        $bet = $this->placeBet($user, 500);

        $bet->update(['status' => 'won', 'payout' => 1000, 'settled_at' => now()]);

        $this->actingAs($admin)
            ->from(route('admin.bets.show', $bet))
            ->put("/admin/bets/{$bet->id}", ['stake' => 800, 'odds' => 2.50])
            ->assertSessionHasErrors('bet');

        $bet->refresh();

        $this->assertSame(500.0, (float) $bet->stake);
        $this->assertSame('won', $bet->status);
        $this->assertSame(4500.0, (float) $user->fresh()->balance);
    }

    public function test_edit_form_is_only_shown_for_open_tickets(): void
    {
        $admin = $this->admin();
        $user = $this->client(5000);
        $bet = $this->placeBet($user, 500);

        $this->actingAs($admin)
            ->get(route('admin.bets.show', $bet))
            ->assertOk()
            ->assertSee('Edit bet')
            ->assertSee('Save changes')
            ->assertSee('selection_id', false);

        $bet->update(['status' => 'lost', 'payout' => 0, 'settled_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.bets.show', $bet))
            ->assertOk()
            ->assertSee('editing is locked');
    }

    public function test_admin_can_move_bet_to_another_selection_in_the_same_match(): void
    {
        $admin = $this->admin();
        $user = $this->client(5000);
        [$match, $market, $home, $away] = $this->fixture(2.00, 3.00);

        $this->actingAs($user)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $bet = Bet::latest('id')->firstOrFail();

        $this->assertSame('Home FC', $bet->selection_name);

        $this->actingAs($admin)
            ->put("/admin/bets/{$bet->id}", [
                'stake' => 500,
                'odds' => 3.00,
                'selection_id' => $away->id,
            ])
            ->assertRedirect(route('admin.bets.show', $bet));

        $bet->refresh();

        $this->assertSame($away->id, $bet->selection_id);
        $this->assertSame('Away FC', $bet->selection_name);
        $this->assertSame($market->id, $bet->market_id);
        $this->assertSame('Match Winner', $bet->market_name);
        $this->assertSame(1500.0, (float) $bet->potential_return);
        $this->assertSame('pending', $bet->status);

        // Stake did not move, so the wallet and exposure stay untouched.
        $this->assertSame(4500.0, (float) $user->fresh()->balance);
        $this->assertSame(500.0, (float) $user->fresh()->exposure);
    }

    public function test_selection_from_another_match_is_rejected(): void
    {
        $admin = $this->admin();
        $user = $this->client(5000);
        [$match, , $home] = $this->fixture(2.00, 3.00);
        [, , $otherHome] = $this->fixture(2.00, 3.00);

        $this->actingAs($user)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $bet = Bet::latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.bets.show', $bet))
            ->put("/admin/bets/{$bet->id}", [
                'stake' => 500,
                'odds' => 2.00,
                'selection_id' => $otherHome->id,
            ])
            ->assertSessionHasErrors('selection_id');

        $bet->refresh();

        $this->assertSame('Home FC', $bet->selection_name);
        $this->assertSame($home->id, $bet->selection_id);
    }

    public function test_selection_in_a_closed_market_is_rejected(): void
    {
        $admin = $this->admin();
        $user = $this->client(5000);
        [, $market, $home, $away] = $this->fixture(2.00, 3.00);

        $this->actingAs($user)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $bet = Bet::latest('id')->firstOrFail();

        $market->update(['status' => 'settled']);

        $this->actingAs($admin)
            ->from(route('admin.bets.show', $bet))
            ->put("/admin/bets/{$bet->id}", [
                'stake' => 500,
                'odds' => 3.00,
                'selection_id' => $away->id,
            ])
            ->assertSessionHasErrors('selection_id');

        $this->assertSame($home->id, $bet->fresh()->selection_id);
    }

    public function test_edited_ticket_shows_corrected_selection_everywhere(): void
    {
        $admin = $this->admin();
        $agent = $this->agent();
        $user = $this->client(5000, $agent);
        [$match, , $home, $away] = $this->fixture(2.00, 3.00);

        $this->actingAs($user)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $bet = Bet::latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->put("/admin/bets/{$bet->id}", ['stake' => 500, 'odds' => 3.00, 'selection_id' => $away->id])
            ->assertRedirect(route('admin.bets.show', $bet));

        // Admin ticket, all-bets list, pending list, match control.
        $this->actingAs($admin)->get(route('admin.bets.show', $bet))->assertOk()->assertSee('Away FC');
        $this->actingAs($admin)->get(route('admin.bets.index'))->assertOk()->assertSee('Away FC');
        $this->actingAs($admin)->get(route('admin.sports.current-bets'))->assertOk()->assertSee('Away FC');
        $this->actingAs($admin)->get(route('admin.matches.show', $match))->assertOk()->assertSee('Away FC');

        // Agent downline bets.
        $this->actingAs($agent)->get(route('agent.bets.index'))->assertOk()->assertSee('Away FC');

        // Player's own ticket list.
        $this->actingAs($user)->get(route('bets.index'))->assertOk()->assertSee('Away FC');
    }
}
