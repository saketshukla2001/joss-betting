<?php

namespace Tests\Feature;

use App\Models\Bet;
use App\Models\DepositRequest;
use App\Models\Market;
use App\Models\MatchGame;
use App\Models\Selection;
use App\Models\Sport;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BettingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function client(float $balance = 1000): User
    {
        return User::create([
            'name' => 'Test Player',
            'username' => 'player'.random_int(1000, 9999),
            'email' => 'player'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => $balance,
        ]);
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

    private function market(float $homeOdds = 2.00, float $awayOdds = 3.00): array
    {
        $sport = Sport::create([
            'name' => 'Football',
            'slug' => 'football',
            'icon' => '⚽',
            'active' => true,
        ]);

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

        return [$market, $home, $away];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_client_can_login_and_view_dashboard(): void
    {
        $user = $this->client();

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'Password@123',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk()->assertSee('Welcome back');
    }

    public function test_non_admin_cannot_open_admin_panel(): void
    {
        $this->actingAs($this->client())->get('/admin')->assertForbidden();
    }

    public function test_client_can_place_a_single_bet(): void
    {
        $user = $this->client(1000);
        [$market, $home] = $this->market();

        $response = $this->actingAs($user)->postJson('/bets', [
            'selection_id' => $home->id,
            'stake' => 500,
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertSame(500.0, $user->fresh()->balance);
        $this->assertSame(500.0, $user->fresh()->exposure);
        $this->assertDatabaseHas('bets', [
            'user_id' => $user->id,
            'selection_id' => $home->id,
            'stake' => 500,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'bet',
            'amount' => -500,
        ]);
    }

    public function test_bet_below_minimum_is_rejected_without_touching_balance(): void
    {
        $user = $this->client(1000);
        [$market, $home] = $this->market();

        $this->actingAs($user)->postJson('/bets', [
            'selection_id' => $home->id,
            'stake' => 50,
        ])->assertStatus(422)->assertJsonValidationErrors('stake');

        $this->assertSame(1000.0, $user->fresh()->balance);
        $this->assertSame(0.0, $user->fresh()->exposure);
        $this->assertDatabaseCount('bets', 0);
    }

    public function test_insufficient_balance_is_rejected(): void
    {
        $user = $this->client(100);
        [$market, $home] = $this->market();

        $this->actingAs($user)->postJson('/bets', [
            'selection_id' => $home->id,
            'stake' => 500,
        ])->assertStatus(422)->assertJsonValidationErrors('stake');

        $this->assertSame(100.0, $user->fresh()->balance);
    }

    public function test_settling_a_market_pays_winners_and_closes_losers(): void
    {
        $winner = $this->client(1000);
        $loser = $this->client(1000);
        [$market, $home, $away] = $this->market(2.00, 3.00);

        $this->actingAs($winner)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $this->actingAs($loser)->postJson('/bets', ['selection_id' => $away->id, 'stake' => 400])->assertOk();

        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/markets/{$market->id}/settle", [
            'mode' => 'win',
            'winning_selection_id' => $home->id,
        ])->assertRedirect();

        $this->assertSame(1500.0, $winner->fresh()->balance);
        $this->assertSame(0.0, $winner->fresh()->exposure);
        $this->assertSame(600.0, $loser->fresh()->balance);
        $this->assertSame(0.0, $loser->fresh()->exposure);

        $winnerBet = Bet::where('user_id', $winner->id)->first();
        $loserBet = Bet::where('user_id', $loser->id)->first();

        $this->assertSame('won', $winnerBet->status);
        $this->assertSame(1000.0, $winnerBet->payout);
        $this->assertSame('lost', $loserBet->status);
        $this->assertSame('settled', $market->fresh()->status);
        $this->assertSame('settled', MatchGame::first()->status);
    }

    public function test_voiding_a_market_refunds_pending_stakes(): void
    {
        $user = $this->client(1000);
        [$market, $home] = $this->market();

        $this->actingAs($user)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();

        $this->actingAs($this->admin())->post("/admin/markets/{$market->id}/settle", [
            'mode' => 'void',
        ])->assertRedirect();

        $this->assertSame(1000.0, $user->fresh()->balance);
        $this->assertSame(0.0, $user->fresh()->exposure);
        $this->assertSame('void', Bet::first()->status);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'refund',
            'amount' => 500,
        ]);
    }

    public function test_admin_can_approve_a_deposit_and_credit_wallet(): void
    {
        $user = $this->client(1000);
        $deposit = DepositRequest::create([
            'user_id' => $user->id,
            'amount' => 2500,
            'method' => 'upi',
            'transaction_ref' => 'UPI-TEST-001',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->post("/admin/payments/deposits/{$deposit->id}/approve", ['admin_note' => 'ok'])
            ->assertRedirect();

        $this->assertSame(3500.0, $user->fresh()->balance);
        $this->assertSame('approved', $deposit->fresh()->status);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 2500,
            'status' => 'completed',
        ]);
    }

    public function test_withdrawal_holds_funds_and_rejection_refunds_them(): void
    {
        $user = $this->client(1000);
        $admin = $this->admin();

        $request = app(PaymentService::class)->requestWithdrawal($user, [
            'amount' => 500,
            'method' => 'upi',
            'account_name' => 'Test Player',
            'account_number' => '9876543210',
            'upi_id' => 'test@upi',
        ]);

        $this->assertSame(500.0, $user->fresh()->balance);

        $this->actingAs($admin)
            ->post("/admin/payments/withdrawals/{$request->id}/reject", ['admin_note' => 'name mismatch'])
            ->assertRedirect();

        $this->assertSame(1000.0, $user->fresh()->balance);
        $this->assertSame('rejected', $request->fresh()->status);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'refund',
            'amount' => 500,
            'status' => 'completed',
        ]);
    }

    public function test_registration_creates_a_client_with_welcome_bonus(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Player',
            'username' => 'newplayer',
            'email' => 'newplayer@example.test',
            'phone' => '9876543210',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'terms' => '1',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('username', 'newplayer')->firstOrFail();

        $this->assertSame('client', $user->role);
        $this->assertSame(500.0, $user->balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'bonus',
            'amount' => 500,
        ]);
    }
}
