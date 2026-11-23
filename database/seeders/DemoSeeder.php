<?php

namespace Database\Seeders;

use App\Models\DepositRequest;
use App\Models\Market;
use App\Models\MatchGame;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use App\Services\BettingService;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $users = $this->seedUsers();
        $sports = $this->seedSports();
        $matches = $this->seedMatches($sports);
        $this->seedPayments($users);
        $this->seedBets($users, $matches);
        $this->seedWithdrawals($users);
        $this->call(AgentSeeder::class);
        $this->call(HierarchyDemoSeeder::class);
        $this->call(StatementDemoSeeder::class);
        $this->call(OperationLogDemoSeeder::class);
        $this->call(LoginLogDemoSeeder::class);
    }

    private function seedSettings(): void
    {
        $values = [
            'site_name' => 'BetZone',
            'tagline' => 'Live sports. Real odds. Instant payouts.',
            'currency' => '₹',
            'min_stake' => '100',
            'max_stake' => '100000',
            'signup_bonus' => '500',
            'support_email' => 'support@betzone.test',
            'announcement' => '🏆 Featured markets are live — back your team and win big this week.',
            'odds_auto_move' => '1',
            'min_deposit' => '500',
            'min_withdrawal' => '100',
            'maintenance_message' => '',
            'rules' => 'Bet responsibly. 18+ only. All bets are subject to market settlement and platform rules.',
        ];

        foreach ($values as $name => $value) {
            Setting::updateOrCreate(['name' => $name], ['value' => $value]);
        }
    }

    private function seedUsers(): array
    {
        $admin = User::create([
            'name' => 'Aarav Sharma',
            'username' => 'admin',
            'email' => 'admin@betzone.test',
            'phone' => '9876543210',
            'password' => 'Admin@12345',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $demo = User::create([
            'name' => 'Demo Player',
            'username' => 'demo',
            'email' => 'demo@betzone.test',
            'phone' => '9812345678',
            'password' => 'Client@12345',
            'role' => 'client',
            'status' => 'active',
        ]);

        $rahul = User::create([
            'name' => 'Rahul Verma',
            'username' => 'rahul',
            'email' => 'rahul@betzone.test',
            'phone' => '9822334455',
            'password' => 'Rahul@12345',
            'role' => 'client',
            'status' => 'active',
        ]);

        $priya = User::create([
            'name' => 'Priya Nair',
            'username' => 'priya',
            'email' => 'priya@betzone.test',
            'phone' => '9833445566',
            'password' => 'Priya@12345',
            'role' => 'client',
            'status' => 'active',
        ]);

        $vikram = User::create([
            'name' => 'Vikram Singh',
            'username' => 'vikram',
            'email' => 'vikram@betzone.test',
            'phone' => '9844556677',
            'password' => 'Vikram@12345',
            'role' => 'client',
            'status' => 'active',
        ]);

        $blocked = User::create([
            'name' => 'Karan Mehta',
            'username' => 'karan',
            'email' => 'karan@betzone.test',
            'phone' => '9855667788',
            'password' => 'Karan@12345',
            'role' => 'client',
            'status' => 'blocked',
        ]);

        $wallet = app(WalletService::class);

        $wallet->credit($demo, 15000, 'admin_credit', 'Opening demo balance');
        $wallet->credit($rahul, 3500, 'admin_credit', 'Opening demo balance');
        $wallet->credit($priya, 12000, 'admin_credit', 'Opening demo balance');
        $wallet->credit($vikram, 3200, 'admin_credit', 'Opening demo balance');
        $wallet->credit($blocked, 1500, 'admin_credit', 'Opening demo balance');

        return compact('admin', 'demo', 'rahul', 'priya', 'vikram', 'blocked');
    }

    private function seedSports(): array
    {
        $rows = [
            ['name' => 'Football', 'slug' => 'football', 'icon' => '⚽', 'sort_order' => 1],
            ['name' => 'Cricket', 'slug' => 'cricket', 'icon' => '🏏', 'sort_order' => 2],
            ['name' => 'Basketball', 'slug' => 'basketball', 'icon' => '🏀', 'sort_order' => 3],
            ['name' => 'Tennis', 'slug' => 'tennis', 'icon' => '🎾', 'sort_order' => 4],
            ['name' => 'Hockey', 'slug' => 'hockey', 'icon' => '🏑', 'sort_order' => 5],
        ];

        $sports = [];

        foreach ($rows as $row) {
            $sports[$row['slug']] = Sport::create($row + ['active' => true]);
        }

        return $sports;
    }

    private function seedMatches(array $sports): array
    {
        $matches = [];

        $arsenal = $this->makeMatch($sports['football'], 'Premier League', 'Arsenal', 'Chelsea', now()->addHours(3), 'upcoming', true);
        $this->market($arsenal, 'Match Winner', true, [
            ['Arsenal', 2.10, 'home'],
            ['Draw', 3.40, 'draw'],
            ['Chelsea', 3.25, 'away'],
        ]);
        $this->market($arsenal, 'Total Goals — Over/Under 2.5', false, [
            ['Over 2.5', 1.85, 'over'],
            ['Under 2.5', 1.95, 'under'],
        ]);
        $this->market($arsenal, 'Both Teams to Score', false, [
            ['Yes', 1.72, 'yes'],
            ['No', 2.05, 'no'],
        ]);
        $matches['arsenal'] = $arsenal;

        $elClasico = $this->makeMatch($sports['football'], 'La Liga', 'Real Madrid', 'Barcelona', now()->addDay()->setTime(21, 0), 'upcoming', true);
        $this->market($elClasico, 'Match Winner', true, [
            ['Real Madrid', 2.35, 'home'],
            ['Draw', 3.20, 'draw'],
            ['Barcelona', 2.90, 'away'],
        ]);
        $this->market($elClasico, 'Total Goals — Over/Under 3.5', false, [
            ['Over 3.5', 2.20, 'over'],
            ['Under 3.5', 1.65, 'under'],
        ]);
        $matches['elclasico'] = $elClasico;

        $inter = $this->makeMatch($sports['football'], 'Serie A', 'Inter Milan', 'Juventus', now()->subMinutes(38), 'live', true);
        $inter->update(['home_score' => 1, 'away_score' => 1]);
        $this->market($inter, 'Match Winner', true, [
            ['Inter Milan', 1.95, 'home'],
            ['Draw', 3.10, 'draw'],
            ['Juventus', 3.60, 'away'],
        ]);
        $this->market($inter, 'Total Goals — Over/Under 2.5', false, [
            ['Over 2.5', 1.55, 'over'],
            ['Under 2.5', 2.40, 'under'],
        ]);
        $matches['inter'] = $inter;

        $liverpool = $this->makeMatch($sports['football'], 'FA Cup', 'Liverpool', 'Manchester City', now()->subDays(2), 'upcoming', false);
        $this->market($liverpool, 'Match Winner', true, [
            ['Liverpool', 2.20, 'home'],
            ['Draw', 3.30, 'draw'],
            ['Manchester City', 3.10, 'away'],
        ]);
        $matches['liverpool'] = $liverpool;

        $bayern = $this->makeMatch($sports['football'], 'Bundesliga', 'Bayern Munich', 'Borussia Dortmund', now()->addDays(2)->setTime(20, 30), 'upcoming', false);
        $this->market($bayern, 'Match Winner', true, [
            ['Bayern Munich', 1.65, 'home'],
            ['Draw', 4.10, 'draw'],
            ['Borussia Dortmund', 4.60, 'away'],
        ]);
        $matches['bayern'] = $bayern;

        $ipl = $this->makeMatch($sports['cricket'], 'Indian Premier League', 'Mumbai Indians', 'Chennai Super Kings', now()->addHours(5), 'upcoming', true);
        $this->market($ipl, 'Match Winner', true, [
            ['Mumbai Indians', 1.90, 'home'],
            ['Chennai Super Kings', 1.90, 'away'],
        ]);
        $this->market($ipl, 'Total Runs — Over/Under 340.5', false, [
            ['Over 340.5', 1.85, 'over'],
            ['Under 340.5', 1.95, 'under'],
        ]);
        $matches['ipl'] = $ipl;

        $india = $this->makeMatch($sports['cricket'], 'ODI Series', 'India', 'Australia', now()->subHour(), 'live', true);
        $india->update(['home_score' => 156, 'away_score' => 142]);
        $this->market($india, 'Match Winner', true, [
            ['India', 1.72, 'home'],
            ['Australia', 2.10, 'away'],
        ]);
        $this->market($india, 'Total Runs — Over/Under 340.5', false, [
            ['Over 340.5', 1.92, 'over'],
            ['Under 340.5', 1.88, 'under'],
        ]);
        $matches['india'] = $india;

        $nba = $this->makeMatch($sports['basketball'], 'NBA', 'Los Angeles Lakers', 'Boston Celtics', now()->addHours(8), 'upcoming', true);
        $this->market($nba, 'Match Winner', true, [
            ['Los Angeles Lakers', 1.75, 'home'],
            ['Boston Celtics', 2.05, 'away'],
        ]);
        $this->market($nba, 'Total Points — Over/Under 220.5', false, [
            ['Over 220.5', 1.90, 'over'],
            ['Under 220.5', 1.90, 'under'],
        ]);
        $matches['nba'] = $nba;

        $atp = $this->makeMatch($sports['tennis'], 'ATP Finals', 'Carlos Alcaraz', 'Jannik Sinner', now()->addDays(1)->setTime(19, 0), 'upcoming', true);
        $this->market($atp, 'Match Winner', true, [
            ['Carlos Alcaraz', 1.80, 'home'],
            ['Jannik Sinner', 2.00, 'away'],
        ]);
        $matches['atp'] = $atp;

        $nhl = $this->makeMatch($sports['hockey'], 'NHL', 'Toronto Maple Leafs', 'Montreal Canadiens', now()->addHours(6), 'upcoming', false);
        $this->market($nhl, 'Match Winner', true, [
            ['Toronto Maple Leafs', 1.85, 'home'],
            ['Montreal Canadiens', 1.95, 'away'],
        ]);
        $matches['nhl'] = $nhl;

        $cancelled = $this->makeMatch($sports['football'], 'EFL Cup', 'Newcastle United', 'Brighton', now()->addHours(12), 'cancelled', false);
        $cancelled->markets()->create(['name' => 'Match Winner', 'auto_settle' => true, 'status' => 'void', 'sort_order' => 1]);
        $matches['cancelled'] = $cancelled;

        return $matches;
    }

    private function seedPayments(array $users): void
    {
        $payments = app(PaymentService::class);
        $admin = $users['admin'];

        $demoDeposit = $payments->requestDeposit($users['demo'], [
            'amount' => 10000,
            'method' => 'upi',
            'transaction_ref' => 'UPI-8899221100',
        ]);
        $payments->approveDeposit($demoDeposit, $admin, 'Verified against UPI reference.');

        $rahulDeposit = $payments->requestDeposit($users['rahul'], [
            'amount' => 5000,
            'method' => 'bank',
            'transaction_ref' => 'NEFT-77120045',
        ]);
        $payments->approveDeposit($rahulDeposit, $admin, 'Bank transfer received.');

        $payments->requestDeposit($users['demo'], [
            'amount' => 5000,
            'method' => 'upi',
            'transaction_ref' => 'UPI-5566112233',
        ]);

        $rejected = $payments->requestDeposit($users['priya'], [
            'amount' => 2000,
            'method' => 'usdt',
            'transaction_ref' => 'TX-0x9f2a',
        ]);
        $payments->rejectDeposit($rejected, $admin, 'Transaction hash could not be verified.');

        $payments->requestDeposit($users['vikram'], [
            'amount' => 7500,
            'method' => 'bank',
            'transaction_ref' => 'IMPS-44556677',
        ]);
    }

    private function seedBets(array $users, array $matches): void
    {
        $betting = app(BettingService::class);

        $pending = [
            ['demo', 'arsenal', 'Match Winner', 'Arsenal', 2000],
            ['demo', 'elclasico', 'Match Winner', 'Real Madrid', 1500],
            ['demo', 'india', 'Match Winner', 'India', 1000],
            ['demo', 'nba', 'Match Winner', 'Los Angeles Lakers', 500],
            ['rahul', 'arsenal', 'Match Winner', 'Chelsea', 1000],
            ['rahul', 'ipl', 'Match Winner', 'Chennai Super Kings', 2000],
            ['rahul', 'atp', 'Match Winner', 'Carlos Alcaraz', 750],
            ['priya', 'elclasico', 'Match Winner', 'Barcelona', 3000],
            ['priya', 'inter', 'Match Winner', 'Inter Milan', 1200],
            ['priya', 'atp', 'Match Winner', 'Jannik Sinner', 800],
            ['vikram', 'nhl', 'Match Winner', 'Toronto Maple Leafs', 500],
        ];

        $created = [];

        foreach ($pending as $index => [$username, $matchKey, $marketName, $selectionName, $stake]) {
            $bet = $this->place($betting, $users[$username], $matches[$matchKey], $marketName, $selectionName, $stake);
            $created[] = $bet;
        }

        $settledBets = [
            ['demo', 'liverpool', 'Match Winner', 'Liverpool', 3000],
            ['rahul', 'liverpool', 'Match Winner', 'Manchester City', 2500],
            ['priya', 'liverpool', 'Match Winner', 'Liverpool', 1000],
            ['vikram', 'liverpool', 'Match Winner', 'Manchester City', 500],
        ];

        $settled = [];

        foreach ($settledBets as [$username, $matchKey, $marketName, $selectionName, $stake]) {
            $settled[] = $this->place($betting, $users[$username], $matches[$matchKey], $marketName, $selectionName, $stake);
        }

        $betting->declareResult($matches['liverpool'], 'home');

        foreach ($created as $index => $bet) {
            if ($index % 3 === 1) {
                $days = ($index % 5) + 1;
                $when = now()->subDays($days);
                DB::table('bets')->where('id', $bet->id)->update(['created_at' => $when, 'updated_at' => $when]);
                DB::table('wallet_transactions')
                    ->where('reference_type', 'bet')
                    ->where('reference_id', $bet->id)
                    ->update(['created_at' => $when, 'updated_at' => $when]);
            }
        }
    }

    private function seedWithdrawals(array $users): void
    {
        $payments = app(PaymentService::class);
        $admin = $users['admin'];

        $demoWithdrawal = $payments->requestWithdrawal($users['demo'], [
            'amount' => 4000,
            'method' => 'upi',
            'account_name' => 'Demo Player',
            'account_number' => '9876543210',
            'upi_id' => 'demo@upi',
        ]);

        $rahulWithdrawal = $payments->requestWithdrawal($users['rahul'], [
            'amount' => 1000,
            'method' => 'bank',
            'account_name' => 'Rahul Verma',
            'account_number' => '50100200300400',
            'ifsc' => 'HDFC0001234',
        ]);
        $payments->approveWithdrawal($rahulWithdrawal, $admin, 'Paid via NEFT.');

        $priyaWithdrawal = $payments->requestWithdrawal($users['priya'], [
            'amount' => 1500,
            'method' => 'upi',
            'account_name' => 'Priya Nair',
            'account_number' => '9822334455',
            'upi_id' => 'priya@upi',
        ]);
        $payments->rejectWithdrawal($priyaWithdrawal, $admin, 'UPI ID name mismatch. Please update KYC.');
    }

    private function makeMatch(Sport $sport, string $league, string $home, string $away, $start, string $status, bool $featured): MatchGame
    {
        return MatchGame::create([
            'sport_id' => $sport->id,
            'league' => $league,
            'home_team' => $home,
            'away_team' => $away,
            'start_time' => $start,
            'status' => $status,
            'featured' => $featured,
        ]);
    }

    private function market(MatchGame $match, string $name, bool $autoSettle, array $selections): Market
    {
        $market = $match->markets()->create([
            'name' => $name,
            'auto_settle' => $autoSettle,
            'status' => 'open',
            'sort_order' => (int) $match->markets()->max('sort_order') + 1,
        ]);

        foreach ($selections as $index => [$selectionName, $odds, $resultKey]) {
            $market->selections()->create([
                'name' => $selectionName,
                'odds' => $odds,
                'result_key' => $resultKey,
                'status' => 'active',
                'sort_order' => $index + 1,
            ]);
        }

        return $market;
    }

    private function place(BettingService $betting, User $user, MatchGame $match, string $marketName, string $selectionName, float $stake)
    {
        $market = $match->markets()->where('name', $marketName)->firstOrFail();
        $selection = $market->selections()->where('name', $selectionName)->firstOrFail();

        return $betting->placeBet($user->fresh(), $selection->id, $stake, '127.0.0.1');
    }
}
