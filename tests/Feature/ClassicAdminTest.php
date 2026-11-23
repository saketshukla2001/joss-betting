<?php

namespace Tests\Feature;

use App\Models\MatchGame;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassicAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'MAU NP',
            'username' => 'admin'.random_int(1000, 9999),
            'email' => 'admin'.random_int(1000, 9999).'@example.test',
            'password' => 'Password@123',
            'role' => 'admin',
            'status' => 'active',
            'agent_code' => 'SAD'.random_int(10000, 99999),
            'share_percent' => 95,
            'company_share_percent' => 5,
            'commission_rate' => 2,
            'session_commission_percent' => 3,
            'balance' => 2199109,
            'secure_code' => '123456',
        ]);
    }

    public function test_admin_dashboard_renders_the_exact_menu_sequence(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSeeInOrder([
            'Dashboard',
            'Admin Details',
            'MINI MASTER',
            'MASTER MASTER',
            'SUPER MASTER',
            'AGENT MASTER',
            'CLIENT MASTER',
            'Sports Betting',
            'INPLAY GAMES',
            'COMPLETED GAMES',
            'CURRENT BETS',
            'Ledger',
            'PROFIT/LOSS',
            'MY LEDGER',
            'MINI LEDGER',
            'MASTER LEDGER',
            'SUPER LEDGER',
            'AGENT LEDGER',
            'CLIENT LEDGER',
            'Cash Transaction',
            'Comm. Report',
            'Reports',
            'SECURE CODE REPORT',
            'Setting',
        ]);
        $response->assertDontSee('Casino');
        $response->assertDontSee('Matka');
    }

    public function test_all_hierarchy_level_pages_load(): void
    {
        $admin = $this->admin();

        foreach (['mini_master', 'master_master', 'super_master', 'agent_master', 'client_master'] as $level) {
            $this->actingAs($admin)->get('/admin/details/'.$level)->assertOk();
        }
    }

    public function test_admin_creates_a_mini_master_with_opening_coins(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/details/mini_master', [
            'name' => 'New Mini',
            'reference' => 'MAU NP',
            'coins' => 5000,
            'share_percent' => 90,
            'commission_type' => 'bet_by_bet',
            'commission_rate' => 2,
            'session_commission_percent' => 3,
            'casino_commission_percent' => 2,
            'matka_commission_percent' => 2,
            'password' => 'AB12C',
        ])->assertRedirect();

        $mini = User::where('role', 'mini_master')->firstOrFail();

        $this->assertSame('mini_master', $mini->role);
        $this->assertSame(5000.0, $mini->balance);
        $this->assertSame($admin->id, $mini->parent_id);
        $this->assertNotEmpty($mini->secure_code);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $mini->id,
            'type' => 'admin_credit',
            'amount' => 5000,
        ]);
    }

    public function test_all_five_level_forms_create_the_correct_account_fields(): void
    {
        $admin = $this->admin();
        $levels = [
            'mini_master' => 'mini_master',
            'master_master' => 'master_master',
            'super_master' => 'super_master',
            'agent_master' => 'agent',
            'client_master' => 'client',
        ];

        foreach ($levels as $level => $role) {
            $name = 'Form Test '.$level;

            $this->actingAs($admin)->post('/admin/details/'.$level, [
                'name' => $name,
                'reference' => 'Mau NP',
                'coins' => 1000,
                'share_percent' => 88,
                'commission_type' => 'bet_by_bet',
                'commission_rate' => 2.5,
                'session_commission_percent' => 3.5,
                'casino_commission_percent' => 1.5,
                'matka_commission_percent' => 1.0,
                'password' => 'AB12C',
            ])->assertRedirect();

            $user = User::where('name', $name)->firstOrFail();

            $this->assertSame($role, $user->role);
            $this->assertSame('Mau NP', $user->reference);
            $this->assertSame(1000.0, $user->balance);
            $this->assertSame(88.0, $user->share_percent);
            $this->assertSame(2.5, $user->commission_rate);
            $this->assertSame(3.5, $user->session_commission_percent);
            $this->assertSame(1.5, $user->casino_commission_percent);
            $this->assertSame(1.0, $user->matka_commission_percent);
            $this->assertSame('bet_by_bet', $user->commission_type);
            $this->assertSame($admin->id, $user->parent_id);
            $this->assertNotEmpty($user->agent_code);
            $this->assertNotEmpty($user->secure_code);
        }
    }

    public function test_statement_page_supports_single_and_bulk_delete(): void
    {
        $admin = $this->admin();

        $first = \App\Models\WalletTransaction::create([
            'user_id' => $admin->id, 'type' => 'admin_credit', 'amount' => 500,
            'balance_after' => 2199609, 'status' => 'completed', 'is_demo' => true,
            'reference_type' => 'demo_statement', 'description' => 'DEMO ONE',
        ]);
        $second = \App\Models\WalletTransaction::create([
            'user_id' => $admin->id, 'type' => 'admin_debit', 'amount' => -250,
            'balance_after' => 2199359, 'status' => 'completed', 'is_demo' => true,
            'reference_type' => 'demo_statement', 'description' => 'DEMO TWO',
        ]);

        $this->actingAs($admin)->get('/admin/settings/statement')->assertOk()->assertSee('DEMO ONE');

        $this->actingAs($admin)->delete('/admin/settings/statement/'.$first->id)->assertRedirect();
        $this->assertDatabaseMissing('wallet_transactions', ['id' => $first->id]);

        $this->actingAs($admin)->post('/admin/settings/statement/bulk-delete', [
            'ids' => [$second->id],
        ])->assertRedirect();
        $this->assertDatabaseMissing('wallet_transactions', ['id' => $second->id]);
    }

    public function test_settings_tiles_account_operations_and_client_search_work(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/settings')->assertOk()
            ->assertSee('Statement')->assertSee('Account Operations')
            ->assertSee('Profit &amp; Loss', false)->assertSee('Casino Profit Loss')->assertSee('Search Client');

        $this->actingAs($admin)->get('/admin/settings/account-operations')->assertOk();
        $this->actingAs($admin)->get('/admin/settings/casino-profit-loss')->assertOk();
        $this->actingAs($admin)->get('/admin/settings/search-client')->assertOk();

        $client = User::create([
            'name' => 'Search Client', 'username' => 'searchclient', 'email' => 'searchclient@example.test',
            'password' => 'Password@123', 'role' => 'client', 'status' => 'active', 'balance' => 100,
            'agent_code' => 'CL9999',
        ]);

        $this->actingAs($admin)->get('/admin/settings/search-client?q=searchclient')->assertOk()->assertSee('Search Client');

        $this->actingAs($admin)->post('/admin/settings/account-operations', [
            'user_id' => $client->id,
            'amount' => 250,
            'direction' => 'credit',
            'note' => 'Welcome coins',
        ])->assertRedirect();

        $this->assertSame(350.0, $client->fresh()->balance);
    }

    public function test_partnership_modal_and_update_limit_page(): void
    {
        $admin = $this->admin();
        $mini = User::create([
            'name' => 'Limit Mini', 'username' => 'limitmini', 'email' => 'limitmini@example.test',
            'password' => 'Password@123', 'role' => 'mini_master', 'status' => 'active',
            'parent_id' => $admin->id, 'agent_code' => 'MINI6666', 'balance' => 100,
            'share_percent' => 88, 'commission_rate' => 2, 'session_commission_percent' => 3,
        ]);

        $this->actingAs($admin)->get('/admin/details/mini_master')->assertOk()
            ->assertSee('PARTNERSHIP DETAILS')->assertSee('data-partnership', false);

        $this->actingAs($admin)->get('/admin/details/mini_master/limits')->assertOk()
            ->assertSee('MINI MASTER UPDATE LIMIT')->assertSee('C.CHIP')->assertSee('ADD / MINUS LIMIT');

        $this->actingAs($admin)->post('/admin/details/mini_master/limits', [
            'user_id' => $mini->id, 'amount' => 250, 'direction' => 'add',
        ])->assertRedirect();
        $this->assertSame(350.0, $mini->fresh()->balance);

        $this->actingAs($admin)->post('/admin/details/mini_master/limits', [
            'user_id' => $mini->id, 'amount' => 100, 'direction' => 'minus',
        ])->assertRedirect();
        $this->assertSame(250.0, $mini->fresh()->balance);

        $this->actingAs($admin)->post('/admin/details/mini_master/limits', [
            'user_id' => $mini->id, 'amount' => 999999, 'direction' => 'minus',
        ])->assertSessionHasErrors('amount');
    }

    public function test_hierarchy_popup_actions_are_wired(): void
    {
        $admin = $this->admin();
        $mini = User::create([
            'name' => 'Action Mini', 'username' => 'actionmini', 'email' => 'actionmini@example.test',
            'password' => 'Password@123', 'role' => 'mini_master', 'status' => 'active',
            'parent_id' => $admin->id, 'agent_code' => 'MINI7777', 'balance' => 0,
            'share_percent' => 90, 'commission_rate' => 2, 'session_commission_percent' => 3,
        ]);

        $this->actingAs($admin)->get('/admin/details/mini_master')->assertOk()
            ->assertSee('DEPOSIT')->assertSee('WITHDRAW')->assertSee('LOGIN REPORT')
            ->assertSee('ACCOUNT OPERATIONS')->assertSee('BLOCK ACTIONS')->assertSee('STATEMENT')
            ->assertSee('RESET PASSWORD');

        $this->actingAs($admin)->post("/admin/details/mini_master/{$mini->id}/cash", [
            'direction' => 'deposit', 'amount' => 500, 'note' => 'Test deposit',
        ])->assertRedirect();
        $this->assertSame(500.0, $mini->fresh()->balance);

        $this->actingAs($admin)->post("/admin/details/mini_master/{$mini->id}/inactive")->assertRedirect();
        $this->assertSame('inactive', $mini->fresh()->status);

        $this->actingAs($admin)->post("/admin/details/mini_master/{$mini->id}/block")->assertRedirect();
        $this->assertSame('blocked', $mini->fresh()->status);

        $oldHash = $mini->fresh()->password;
        $this->actingAs($admin)->post("/admin/details/mini_master/{$mini->id}/reset-password")->assertRedirect();
        $this->assertNotSame($oldHash, $mini->fresh()->password);

        $this->actingAs($admin)->get("/admin/details/mini_master/{$mini->id}/login-report")->assertOk();
        $this->actingAs($admin)->get("/admin/details/mini_master/{$mini->id}/statement")->assertOk();
    }

    public function test_hierarchy_bulk_update_and_single_and_bulk_delete(): void
    {
        $admin = $this->admin();

        $first = User::create([
            'name' => 'Bulk One', 'username' => 'bulkone', 'email' => 'bulkone@example.test',
            'password' => 'Password@123', 'role' => 'mini_master', 'status' => 'active',
            'parent_id' => $admin->id, 'agent_code' => 'MINI8881', 'share_percent' => 90,
        ]);
        $second = User::create([
            'name' => 'Bulk Two', 'username' => 'bulktwo', 'email' => 'bulktwo@example.test',
            'password' => 'Password@123', 'role' => 'mini_master', 'status' => 'active',
            'parent_id' => $admin->id, 'agent_code' => 'MINI8882', 'share_percent' => 90,
        ]);

        $this->actingAs($admin)->post('/admin/details/mini_master/bulk-update', [
            'ids' => [$first->id, $second->id],
            'status' => 'active',
            'share_percent' => 77,
            'commission_type' => 'net_loss',
            'commission_rate' => 4,
            'session_commission_percent' => 5,
        ])->assertRedirect();

        $this->assertSame(77.0, $first->fresh()->share_percent);
        $this->assertSame(4.0, $first->fresh()->commission_rate);

        $this->actingAs($admin)->post('/admin/details/mini_master/bulk-delete', [
            'ids' => [$first->id, $second->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $first->id]);
        $this->assertDatabaseMissing('users', ['id' => $second->id]);
    }

    public function test_secure_code_report_search_and_commission_report_pages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/reports/secure-code')->assertOk()->assertSee('NO DATA');
        $this->actingAs($admin)->get('/admin/reports/secure-code?q=123456')->assertOk()
            ->assertSee($admin->username)->assertSee('123456');

        $this->actingAs($admin)->get('/admin/comm-report')->assertOk()
            ->assertSee('AGENT COMMISSION REPORT')->assertSee('MILA HAI')->assertSee('DIYA HAI')->assertSee('TOTAL');
    }

    public function test_cash_transaction_form_creates_a_client_collection(): void
    {
        $admin = $this->admin();
        $client = User::create([
            'name' => 'Cash Client', 'username' => 'cashclient', 'email' => 'cashclient@example.test',
            'password' => 'Password@123', 'role' => 'client', 'status' => 'active',
            'balance' => 100, 'agent_code' => 'CL7777',
        ]);

        $this->actingAs($admin)->get('/admin/cash-transaction/client_master')->assertOk()
            ->assertSee('CLIENT MASTER TRANSACTION')->assertSee('PAYMENT TYPE');

        $this->actingAs($admin)->post('/admin/cash-transaction/client_master/store', [
            'client_id' => $client->id,
            'collection' => 'CASH',
            'date' => now()->toDateString(),
            'amount' => 500,
            'payment_type' => 'CASH',
            'comment' => 'Cash collected at counter',
        ])->assertRedirect();

        $this->assertSame(600.0, $client->fresh()->balance);
        $this->assertDatabaseHas('operation_logs', ['operation' => 'CASH TRANSACTION']);
    }

    public function test_cash_transaction_credits_and_debits_a_client(): void
    {
        $admin = $this->admin();
        $agent = User::create([
            'name' => 'Agent', 'username' => 'agentx', 'email' => 'agentx@example.test',
            'password' => 'Password@123', 'role' => 'agent', 'status' => 'active',
        ]);
        $client = User::create([
            'name' => 'Client', 'username' => 'clientx', 'email' => 'clientx@example.test',
            'password' => 'Password@123', 'role' => 'client', 'status' => 'active',
            'parent_id' => $agent->id, 'balance' => 1000,
        ]);

        $this->actingAs($admin)->post('/admin/cash-transaction/client_master/adjust', [
            'user_id' => $client->id,
            'amount' => 500,
            'direction' => 'credit',
            'note' => 'Test credit',
        ])->assertRedirect();

        $this->assertSame(1500.0, $client->fresh()->balance);

        $this->actingAs($admin)->post('/admin/cash-transaction/client_master/adjust', [
            'user_id' => $client->id,
            'amount' => 200,
            'direction' => 'debit',
            'note' => 'Test debit',
        ])->assertRedirect();

        $this->assertSame(1300.0, $client->fresh()->balance);
    }

    public function test_super_admin_can_change_panel_colors(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/settings/appearance')->assertOk()
            ->assertSee('PANEL COLOR')->assertSee('PRIMARY')->assertSee('ACCENT');

        $this->actingAs($admin)->post('/admin/settings/appearance', [
            'panel_primary' => '#123456',
            'panel_primary_dark' => '#0a1c2d',
            'panel_primary_mid' => '#1d4f77',
            'panel_accent' => '#f4c542',
            'panel_soft' => '#eaf2fa',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['name' => 'panel_primary', 'value' => '#123456']);
        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('--panel-primary: #123456', false)
            ->assertSee('--panel-accent: #f4c542', false);

        $this->actingAs($admin)->post('/admin/settings/appearance/reset')->assertRedirect();
        $this->assertDatabaseHas('settings', ['name' => 'panel_primary', 'value' => '#1e6b1e']);
    }

    public function test_admin_can_update_share_terms_and_regenerate_secure_code(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/settings/shares', [
            'share_percent' => 92,
            'company_share_percent' => 8,
            'commission_rate' => 2.5,
            'session_commission_percent' => 3.5,
        ])->assertRedirect();

        $this->assertSame(92.0, $admin->fresh()->share_percent);
        $this->assertSame(2.5, $admin->fresh()->commission_rate);

        $this->actingAs($admin)->post('/admin/reports/secure-code/'.$admin->id.'/regenerate')->assertRedirect();

        $this->assertNotSame('123456', $admin->fresh()->secure_code);
    }

    public function test_active_games_menu_match_limits_and_match_bets(): void
    {
        $admin = $this->admin();
        $sport = Sport::create(['name' => 'Cricket', 'slug' => 'cricket-active', 'icon' => '🏏']);
        $match = MatchGame::create([
            'sport_id' => $sport->id, 'league' => 'Active League', 'home_team' => 'Alpha',
            'away_team' => 'Beta', 'start_time' => now()->addHour(), 'status' => 'live',
        ]);
        $master = User::create([
            'name' => 'Match Master', 'username' => 'matchmaster', 'email' => 'matchmaster@example.test',
            'password' => 'Password@123', 'role' => 'master_master', 'status' => 'active',
            'parent_id' => $admin->id, 'agent_code' => 'MAST9001', 'balance' => 1000,
            'share_percent' => 80, 'commission_rate' => 2, 'session_commission_percent' => 3,
        ]);

        $this->actingAs($admin)->get('/admin/sports-betting/inplay')->assertOk()
            ->assertSee('ACTIVE GAMES')->assertSee('MATCH BET')->assertSee('SESSION BET')
            ->assertSee('COMPLETED SESSION')->assertSee('REJECTED BETS')
            ->assertSee('ALPHA V BETA');

        $this->actingAs($admin)->get("/admin/sports-betting/{$match->id}/view")->assertOk()
            ->assertSee('My Book')->assertSee('Tii Book')->assertSee('FANCY (COMM.)')
            ->assertSee('CLIENTS SESSION PL')->assertSee('MATCH STATS')->assertSee('COMPLETED SESSIONS');

        $this->actingAs($admin)->get("/admin/sports-betting/{$match->id}/limits")->assertOk()
            ->assertSee('MASTER UPDATE LIMIT')->assertSee('MATCHMASTER')->assertSee('ADD / MINUS LIMIT');

        $this->actingAs($admin)->post("/admin/sports-betting/{$match->id}/limits", [
            'user_id' => $master->id, 'amount' => 250, 'direction' => 'add',
        ])->assertRedirect();

        $this->assertDatabaseHas('match_limits', [
            'match_id' => $match->id, 'user_id' => $master->id, 'amount' => 250,
        ]);

        foreach (['match', 'session', 'completed-session', 'rejected'] as $type) {
            $this->actingAs($admin)->get("/admin/sports-betting/{$match->id}/bets/{$type}")->assertOk();
        }

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('MATCH BET');
    }

    public function test_completed_games_pl_report_and_summary_calculations(): void
    {
        $admin = $this->admin();
        $sport = Sport::create(['name' => 'Tennis', 'slug' => 'tennis-pl', 'icon' => '🎾']);
        $match = MatchGame::create([
            'sport_id' => $sport->id, 'league' => 'PL League', 'home_team' => 'Gamma',
            'away_team' => 'Delta', 'start_time' => now()->subDay(), 'status' => 'settled',
        ]);
        $client = User::create([
            'name' => 'PL Client', 'username' => 'plclient', 'email' => 'plclient@example.test',
            'password' => 'Password@123', 'role' => 'client', 'status' => 'active',
            'agent_code' => 'C9001', 'balance' => 0,
        ]);

        $matchMarket = \App\Models\Market::create([
            'match_id' => $match->id, 'name' => 'Match Winner', 'auto_settle' => true, 'status' => 'settled',
        ]);
        $matchSelection = \App\Models\Selection::create([
            'market_id' => $matchMarket->id, 'name' => 'Gamma', 'result_key' => 'home', 'odds' => 2.50, 'status' => 'active', 'result' => 'win',
        ]);
        $sessionMarket = \App\Models\Market::create([
            'match_id' => $match->id, 'name' => 'Session Runs', 'auto_settle' => false, 'status' => 'settled',
        ]);
        $sessionSelection = \App\Models\Selection::create([
            'market_id' => $sessionMarket->id, 'name' => 'Over 50', 'result_key' => 'session', 'odds' => 1.80, 'status' => 'active', 'result' => 'lose',
        ]);

        \App\Models\Bet::create([
            'bet_code' => 'PL-WON-1', 'user_id' => $client->id, 'match_id' => $match->id, 'match_label' => 'Gamma vs Delta',
            'market_id' => $matchMarket->id, 'selection_id' => $matchSelection->id,
            'market_name' => 'Match Winner', 'selection_name' => 'Gamma', 'odds' => 2.50,
            'stake' => 100, 'potential_return' => 250, 'status' => 'won', 'payout' => 250,
        ]);
        \App\Models\Bet::create([
            'bet_code' => 'PL-LOST-2', 'user_id' => $client->id, 'match_id' => $match->id, 'match_label' => 'Gamma vs Delta',
            'market_id' => $sessionMarket->id, 'selection_id' => $sessionSelection->id,
            'market_name' => 'Session Runs', 'selection_name' => 'Over 50', 'odds' => 1.80,
            'stake' => 50, 'potential_return' => 90, 'status' => 'lost', 'payout' => 0,
        ]);

        $this->actingAs($admin)->get('/admin/sports-betting/completed')->assertOk()
            ->assertSee('MATCH AND SESSION PL REPORT')->assertSee('MATCH AND SESSION PL')
            ->assertSee('MATCH BET')->assertSee('SESSION BET');

        $this->actingAs($admin)->get("/admin/sports-betting/{$match->id}/pl-report")->assertOk()
            ->assertSee('MATCH AND SESSION PL REPORT')->assertSee('150.00')->assertSee('-50.00')->assertSee('100.00');

        $this->actingAs($admin)->get("/admin/sports-betting/{$match->id}/pl")->assertOk()
            ->assertSee('MATCH AND SESSION PL')->assertSee('Match Winner')->assertSee('Session Runs');
    }

    public function test_ledger_pages_render_match_my_and_level_boards(): void
    {
        $admin = $this->admin();

        foreach ([['miniledgerpos', 500], ['miniledgerneg', -150], ['miniledgerzero', 0]] as [$username, $balance]) {
            User::create([
                'name' => ucfirst($username), 'username' => $username,
                'email' => $username.'@example.test', 'password' => 'Password@123',
                'role' => 'mini_master', 'status' => 'active', 'parent_id' => $admin->id,
                'agent_code' => 'MINI'.random_int(1000, 9999), 'balance' => $balance,
            ]);
        }

        $this->actingAs($admin)->get('/admin/ledger/profit-loss')->assertOk()
            ->assertSee('MATCH LEDGER')->assertSee('EVENT NAME')->assertSee('TOTAL');

        $this->actingAs($admin)->get('/admin/ledger/my')->assertOk()
            ->assertSee('MY LEDGER')->assertSee('LENA')->assertSee('DENA')->assertSee('BALANCE');

        $this->actingAs($admin)->get('/admin/ledger/mini_master')->assertOk()
            ->assertSee('MINI MASTER LEDGER')->assertSee('LENA')->assertSee('DENA')
            ->assertSee('CLEAR')->assertSee('MINILEDGERPOS')->assertSee('MINILEDGERNEG');
    }

    public function test_sports_ledger_commission_and_settings_pages_load(): void
    {
        $admin = $this->admin();
        $sport = Sport::create(['name' => 'Football', 'slug' => 'football-test', 'icon' => '⚽']);
        MatchGame::create([
            'sport_id' => $sport->id, 'league' => 'Test', 'home_team' => 'Home',
            'away_team' => 'Away', 'start_time' => now()->addHour(), 'status' => 'live',
        ]);

        foreach ([
            '/admin/sports-betting/inplay',
            '/admin/sports-betting/completed',
            '/admin/sports-betting/current-bets',
            '/admin/ledger/profit-loss',
            '/admin/ledger/my',
            '/admin/ledger/client_master',
            '/admin/cash-transaction/client_master',
            '/admin/comm-report',
            '/admin/reports/secure-code',
            '/admin/settings',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
