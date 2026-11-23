<?php

namespace Tests\Feature;

use App\Models\AgentCommission;
use App\Models\Market;
use App\Models\MatchGame;
use App\Models\Selection;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentCommissionTest extends TestCase
{
    use RefreshDatabase;

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

    private function agent(float $rate = 10, string $type = 'turnover', ?User $parent = null): User
    {
        return User::create([
            'name' => 'Agent',
            'username' => 'agent'.random_int(1000, 9999),
            'email' => 'agent'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'agent',
            'status' => 'active',
            'parent_id' => $parent?->id,
            'agent_code' => 'AG'.random_int(1000, 9999),
            'commission_rate' => $rate,
            'commission_type' => $type,
        ]);
    }

    private function client(User $agent, float $balance = 1000): User
    {
        return User::create([
            'name' => 'Player',
            'username' => 'player'.random_int(1000, 9999),
            'email' => 'player'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'client',
            'status' => 'active',
            'balance' => $balance,
            'parent_id' => $agent->id,
        ]);
    }

    private function market(): array
    {
        $sport = Sport::create(['name' => 'Football', 'slug' => 'football-'.random_int(1000, 9999), 'icon' => '⚽']);

        $match = MatchGame::create([
            'sport_id' => $sport->id,
            'league' => 'Test League',
            'home_team' => 'Home FC',
            'away_team' => 'Away FC',
            'start_time' => now()->addHour(),
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
            'odds' => 2.00,
            'status' => 'active',
        ]);

        $away = Selection::create([
            'market_id' => $market->id,
            'name' => 'Away FC',
            'result_key' => 'away',
            'odds' => 3.00,
            'status' => 'active',
        ]);

        return [$market, $home, $away];
    }

    public function test_turnover_commission_accrues_when_a_bet_settles(): void
    {
        $agent = $this->agent(10, 'turnover');
        $player = $this->client($agent, 1000);
        [$market, $home] = $this->market();

        $this->actingAs($player)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $this->actingAs($this->admin())->post("/admin/markets/{$market->id}/settle", [
            'mode' => 'win',
            'winning_selection_id' => $home->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('agent_commissions', [
            'agent_id' => $agent->id,
            'player_id' => $player->id,
            'amount' => 50,
            'status' => 'pending',
        ]);

        $this->assertSame(50.0, $agent->fresh()->commission_balance);
        $this->assertSame(0.0, $agent->fresh()->balance);
    }

    public function test_commission_payout_credits_agent_wallet_and_marks_entries_paid(): void
    {
        $agent = $this->agent(10);
        $player = $this->client($agent, 1000);
        [$market, $home] = $this->market();

        $this->actingAs($player)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $this->actingAs($this->admin())->post("/admin/markets/{$market->id}/settle", [
            'mode' => 'win',
            'winning_selection_id' => $home->id,
        ])->assertRedirect();

        $this->actingAs($this->admin())->post("/admin/commissions/payout/{$agent->id}")->assertRedirect();

        $this->assertSame(50.0, $agent->fresh()->balance);
        $this->assertSame(0.0, $agent->fresh()->commission_balance);
        $this->assertSame('paid', AgentCommission::first()->status);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $agent->id,
            'type' => 'admin_credit',
            'amount' => 50,
        ]);
    }

    public function test_net_loss_agent_earns_only_when_player_loses(): void
    {
        $agent = $this->agent(10, 'net_loss');
        $player = $this->client($agent, 1000);
        [$market, $home, $away] = $this->market();

        $this->actingAs($player)->postJson('/bets', ['selection_id' => $away->id, 'stake' => 500])->assertOk();
        $this->actingAs($this->admin())->post("/admin/markets/{$market->id}/settle", [
            'mode' => 'win',
            'winning_selection_id' => $home->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('agent_commissions', [
            'agent_id' => $agent->id,
            'amount' => 50,
        ]);
    }

    public function test_upline_agent_also_earns_commission(): void
    {
        $upline = $this->agent(2);
        $downline = $this->agent(5, 'turnover', $upline);
        $player = $this->client($downline, 1000);
        [$market, $home] = $this->market();

        $this->actingAs($player)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 500])->assertOk();
        $this->actingAs($this->admin())->post("/admin/markets/{$market->id}/settle", [
            'mode' => 'win',
            'winning_selection_id' => $home->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('agent_commissions', ['agent_id' => $downline->id, 'amount' => 25]);
        $this->assertDatabaseHas('agent_commissions', ['agent_id' => $upline->id, 'amount' => 10]);
    }

    public function test_each_role_creates_only_its_next_level(): void
    {
        $admin = $this->admin();
        $mini = User::create([
            'name' => 'Mini Chain', 'username' => 'minichain'.random_int(100, 999),
            'email' => 'minichain'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'mini_master', 'status' => 'active', 'parent_id' => $admin->id,
            'agent_code' => 'MINI'.random_int(1000, 9999), 'balance' => 1000,
            'share_percent' => 90, 'commission_rate' => 2, 'session_commission_percent' => 3,
            'casino_commission_percent' => 2, 'matka_commission_percent' => 2,
        ]);
        $master = User::create([
            'name' => 'Master Chain', 'username' => 'masterchain'.random_int(100, 999),
            'email' => 'masterchain'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'master_master', 'status' => 'active', 'parent_id' => $mini->id,
            'agent_code' => 'MAST'.random_int(1000, 9999), 'balance' => 1000,
            'share_percent' => 80, 'commission_rate' => 2, 'session_commission_percent' => 3,
            'casino_commission_percent' => 2, 'matka_commission_percent' => 2,
        ]);
        $super = User::create([
            'name' => 'Super Chain', 'username' => 'superchain'.random_int(100, 999),
            'email' => 'superchain'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'super_master', 'status' => 'active', 'parent_id' => $master->id,
            'agent_code' => 'SUP'.random_int(1000, 9999), 'balance' => 1000,
            'share_percent' => 70, 'commission_rate' => 2, 'session_commission_percent' => 3,
            'casino_commission_percent' => 2, 'matka_commission_percent' => 2,
        ]);
        $agent = User::create([
            'name' => 'Agent Chain', 'username' => 'agentchain'.random_int(100, 999),
            'email' => 'agentchain'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'agent', 'status' => 'active', 'parent_id' => $super->id,
            'agent_code' => 'AG'.random_int(1000, 9999), 'balance' => 1000,
            'share_percent' => 60, 'commission_rate' => 2, 'session_commission_percent' => 3,
            'casino_commission_percent' => 2, 'matka_commission_percent' => 2,
        ]);

        $cases = [
            [$mini, 'master_master', 'Create Master Master'],
            [$master, 'super_master', 'Create Super Master'],
            [$super, 'agent', 'Create Agent Master'],
            [$agent, 'client', 'Create Client Master'],
        ];

        foreach ($cases as $index => [$actor, $expectedRole, $button]) {
            $this->actingAs($actor)->get('/agent/hierarchy')->assertOk()->assertSee($button);

            $payload = [
                'name' => 'Chain Child '.$index,
                'reference' => 'Mau NP',
                'password' => 'Chain@12345',
                'coins' => 0,
            ];
            if ($expectedRole !== 'client') {
                $payload += [
                    'share_percent' => 50,
                    'commission_type' => 'bet_by_bet',
                    'commission_rate' => 2,
                    'session_commission_percent' => 3,
                    'casino_commission_percent' => 2,
                    'matka_commission_percent' => 2,
                ];
            }

            $this->actingAs($actor)->post('/agent/hierarchy', $payload)->assertRedirect();
            $this->assertDatabaseHas('users', ['parent_id' => $actor->id, 'role' => $expectedRole]);
        }
    }

    public function test_master_can_edit_all_downline_fields(): void
    {
        $admin = $this->admin();
        $mini = User::create([
            'name' => 'Edit Mini', 'username' => 'editmini'.random_int(100, 999),
            'email' => 'editmini'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'mini_master', 'status' => 'active', 'parent_id' => $admin->id,
            'agent_code' => 'MINI'.random_int(1000, 9999), 'balance' => 1000,
            'share_percent' => 90, 'commission_type' => 'bet_by_bet', 'commission_rate' => 2,
            'session_commission_percent' => 3, 'casino_commission_percent' => 2, 'matka_commission_percent' => 2,
        ]);
        $child = User::create([
            'name' => 'Edit Child', 'username' => 'editchild'.random_int(100, 999),
            'email' => 'editchild'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'master_master', 'status' => 'active', 'parent_id' => $mini->id,
            'agent_code' => 'MAST'.random_int(1000, 9999), 'balance' => 250,
            'share_percent' => 80, 'commission_type' => 'bet_by_bet', 'commission_rate' => 2,
            'session_commission_percent' => 3, 'casino_commission_percent' => 2, 'matka_commission_percent' => 2,
        ]);

        $this->actingAs($mini)->get("/agent/hierarchy/{$child->id}/edit")->assertOk()
            ->assertSee('EDIT MASTER MASTER')->assertSee('Edit Child');

        $newUsername = 'updatedchild'.random_int(100, 999);

        $this->actingAs($mini)->put("/agent/hierarchy/{$child->id}", [
            'name' => 'Updated Child',
            'username' => $newUsername,
            'email' => $newUsername.'@example.test',
            'reference' => 'Updated Ref',
            'password' => 'NewPass@123',
            'coins' => 400,
            'status' => 'inactive',
            'share_percent' => 70,
            'commission_type' => 'net_loss',
            'commission_rate' => 1.5,
            'session_commission_percent' => 2,
            'casino_commission_percent' => 1,
            'matka_commission_percent' => 1,
        ])->assertRedirect();

        $child = $child->fresh();
        $this->assertSame('Updated Child', $child->name);
        $this->assertSame($newUsername, $child->username);
        $this->assertSame('inactive', $child->status);
        $this->assertSame(70.0, (float) $child->share_percent);
        $this->assertSame(1.5, (float) $child->commission_rate);
        $this->assertSame(400.0, (float) $child->balance);
        $this->assertSame(850.0, (float) $mini->fresh()->balance);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPass@123', $child->password));
    }

    public function test_master_can_open_matches_and_place_bet(): void
    {
        $admin = $this->admin();
        $agent = $this->agent(10, 'turnover', $admin);
        $agent->update(['balance' => 1000]);

        [$market, $home, $away] = $this->market();
        $match = $market->match;

        $this->actingAs($agent)->get('/agent/matches')->assertOk()->assertSee('MATCHES');
        $this->actingAs($agent)->get("/agent/matches/{$match->id}")->assertOk()
            ->assertSee($match->home_team)->assertSee('Place Bet');

        $this->actingAs($agent)->post('/agent/matches/bet', [
            'selection_id' => $home->id,
            'stake' => 100,
        ])->assertRedirect();

        $this->assertDatabaseHas('bets', [
            'user_id' => $agent->id,
            'selection_id' => $home->id,
            'stake' => 100,
        ]);
        $this->assertSame(900.0, (float) $agent->fresh()->balance);
    }

    public function test_super_admin_controls_modules_for_common_master_panel(): void
    {
        $admin = $this->admin();
        $mini = User::create([
            'name' => 'Access Mini', 'username' => 'accessmini'.random_int(100, 999),
            'email' => 'accessmini'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'mini_master', 'status' => 'active', 'parent_id' => $admin->id,
            'agent_code' => 'MINI'.random_int(1000, 9999), 'balance' => 100,
        ]);

        $this->actingAs($admin)->get("/admin/access/{$mini->id}")->assertOk()
            ->assertSee('PANEL ACCESS')->assertSee('My Network')->assertSee('Downline Bets');

        $this->actingAs($admin)->post("/admin/access/{$mini->id}", [
            'modules' => ['network', 'bets'],
        ])->assertRedirect();

        $this->assertSame(['network', 'bets'], $mini->fresh()->panel_permissions);
        $mini = $mini->fresh();

        $this->actingAs($mini)->get('/agent/hierarchy')->assertOk();
        $this->actingAs($mini)->get('/agent/bets')->assertOk();
        $this->actingAs($mini)->get('/agent/players')->assertForbidden();
        $this->actingAs($mini)->get('/agent/commissions')->assertForbidden();
        $this->actingAs($mini)->get('/agent/wallet')->assertForbidden();
        $this->actingAs($mini)->get('/agent/account')->assertForbidden();

        $this->actingAs($admin)->post("/admin/access/{$mini->id}/reset")->assertRedirect();
        $this->assertNull($mini->fresh()->panel_permissions);
        $mini = $mini->fresh();
        $this->actingAs($mini)->get('/agent/players')->assertOk();
    }

    public function test_master_sees_only_downline_never_upline_or_siblings(): void
    {
        $admin = $this->admin();
        $mini = User::create([
            'name' => 'Scope Mini', 'username' => 'scopemini'.random_int(100, 999),
            'email' => 'scopemini'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'mini_master', 'status' => 'active', 'parent_id' => $admin->id,
            'agent_code' => 'MINI'.random_int(1000, 9999), 'balance' => 100,
        ]);
        $sibling = User::create([
            'name' => 'Sibling Mini', 'username' => 'siblingmini'.random_int(100, 999),
            'email' => 'siblingmini'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'mini_master', 'status' => 'active', 'parent_id' => $admin->id,
            'agent_code' => 'MINI'.random_int(1000, 9999), 'balance' => 100,
        ]);
        $master = User::create([
            'name' => 'Scope Master', 'username' => 'scopemaster'.random_int(100, 999),
            'email' => 'scopemaster'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'master_master', 'status' => 'active', 'parent_id' => $mini->id,
            'agent_code' => 'MAST'.random_int(1000, 9999), 'balance' => 100,
        ]);
        $super = User::create([
            'name' => 'Scope Super', 'username' => 'scopesuper'.random_int(100, 999),
            'email' => 'scopesuper'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'super_master', 'status' => 'active', 'parent_id' => $master->id,
            'agent_code' => 'SUP'.random_int(1000, 9999), 'balance' => 100,
        ]);

        $this->actingAs($mini)->get('/agent/hierarchy')->assertOk()
            ->assertSee('scopemaster')->assertSee('scopesuper')->assertDontSee('siblingmini');

        $this->actingAs($master)->get('/agent/hierarchy')->assertOk()
            ->assertSee('scopesuper')->assertDontSee('siblingmini');
    }

    public function test_sequence_hierarchy_creates_next_level_with_parent_limits(): void
    {
        $admin = $this->admin();
        $mini = User::create([
            'name' => 'Mini Sequence', 'username' => 'miniseq'.random_int(100, 999),
            'email' => 'miniseq'.random_int(100, 999).'@example.test', 'password' => 'Password@123',
            'role' => 'mini_master', 'status' => 'active', 'parent_id' => $admin->id,
            'agent_code' => 'MINI'.random_int(1000, 9999), 'balance' => 1000,
            'share_percent' => 90, 'commission_type' => 'bet_by_bet', 'commission_rate' => 2,
            'session_commission_percent' => 3, 'casino_commission_percent' => 2, 'matka_commission_percent' => 2,
        ]);

        $this->actingAs($mini)->get('/agent/hierarchy')->assertOk()
            ->assertSee('My Network')->assertSee('Create Master Master');
        $this->actingAs($mini)->get('/agent/players/create')->assertForbidden();

        $this->actingAs($mini)->post('/agent/hierarchy', [
            'name' => 'Child Master',
            'reference' => 'Mau NP',
            'password' => 'Child@12345',
            'coins' => 250,
            'my_share_percent' => 88,
            'my_commission_type' => 'net_loss',
            'my_commission_rate' => 2,
            'my_session_commission_percent' => 3,
            'my_casino_commission_percent' => 2,
            'my_matka_commission_percent' => 2,
            'share_percent' => 80,
            'commission_type' => 'bet_by_bet',
            'commission_rate' => 2,
            'session_commission_percent' => 3,
            'casino_commission_percent' => 2,
            'matka_commission_percent' => 2,
        ])->assertRedirect();

        $child = User::where('parent_id', $mini->id)->where('role', 'master_master')->firstOrFail();
        $this->assertSame(250.0, (float) $child->balance);
        $this->assertSame(750.0, (float) $mini->fresh()->balance);
        $this->assertSame(80.0, (float) $child->share_percent);
        $this->assertSame(88.0, (float) $mini->fresh()->share_percent);
        $this->assertSame('net_loss', $mini->fresh()->commission_type);

        $this->actingAs($mini)->post('/agent/hierarchy', [
            'name' => 'Too High Share',
            'reference' => 'Mau NP',
            'password' => 'Child@12345',
            'coins' => 0,
            'share_percent' => 95,
            'commission_type' => 'bet_by_bet',
            'commission_rate' => 2,
            'session_commission_percent' => 3,
            'casino_commission_percent' => 2,
            'matka_commission_percent' => 2,
        ])->assertSessionHasErrors('share_percent');

        $agent = $this->agent(10, 'turnover', $mini);
        $agent->update(['balance' => 500]);
        $this->actingAs($agent)->post('/agent/hierarchy', [
            'name' => 'Client From Agent',
            'reference' => 'Mau NP',
            'password' => 'Client@12345',
            'coins' => 100,
        ])->assertRedirect();

        $client = User::where('parent_id', $agent->id)->where('role', 'client')->firstOrFail();
        $this->assertSame(0.0, (float) $client->share_percent);
        $this->assertSame(0.0, (float) $client->commission_rate);
    }

    public function test_agent_can_create_and_view_own_players_only(): void
    {
        $agent = $this->agent();
        $otherAgent = $this->agent();

        $this->actingAs($agent)->post('/agent/players', [
            'name' => 'New Player',
            'username' => 'newplayer'.random_int(1000, 9999),
            'email' => 'new'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ])->assertRedirect();

        $player = User::where('parent_id', $agent->id)->firstOrFail();

        $this->actingAs($agent)->get("/agent/players/{$player->id}")->assertOk();

        $otherPlayer = $this->client($otherAgent);
        $this->actingAs($agent)->get("/agent/players/{$otherPlayer->id}")->assertForbidden();
    }

    public function test_agent_can_withdraw_commission_from_wallet(): void
    {
        $agent = $this->agent(10);
        $player = $this->client($agent, 10000);
        [$market, $home] = $this->market();

        $this->actingAs($player)->postJson('/bets', ['selection_id' => $home->id, 'stake' => 6000])->assertOk();
        $this->actingAs($this->admin())->post("/admin/markets/{$market->id}/settle", [
            'mode' => 'win',
            'winning_selection_id' => $home->id,
        ])->assertRedirect();

        $this->actingAs($this->admin())->post("/admin/commissions/payout/{$agent->id}")->assertRedirect();
        $this->assertSame(600.0, $agent->fresh()->balance);

        $this->actingAs($agent)->get('/agent/wallet')->assertOk();

        $this->actingAs($agent)->post('/agent/wallet/withdraw', [
            'amount' => 500,
            'method' => 'upi',
            'account_name' => 'Agent',
            'account_number' => '9876543210',
            'upi_id' => 'agent@upi',
        ])->assertRedirect();

        $this->assertSame(100.0, $agent->fresh()->balance);
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $agent->id,
            'amount' => 500,
            'status' => 'pending',
        ]);
    }

    public function test_agent_is_kept_out_of_admin_and_client_panels(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->get('/admin')->assertForbidden();
        $this->actingAs($agent)->get('/dashboard')->assertRedirect('/agent');
        $this->actingAs($agent)->get('/agent')->assertOk();
    }

    public function test_admin_can_create_an_agent_with_commission_terms(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/agents', [
            'name' => 'New Agent',
            'username' => 'newagent'.random_int(1000, 9999),
            'email' => 'agent'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'commission_rate' => 7.5,
            'commission_type' => 'turnover',
            'status' => 'active',
        ])->assertRedirect();

        $agent = User::where('role', 'agent')->firstOrFail();
        $this->assertSame(7.5, $agent->commission_rate);
        $this->assertNotEmpty($agent->agent_code);
    }
}
