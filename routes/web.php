<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AccessController as AdminAccessController;
use App\Http\Controllers\Admin\AccountOperationsController as AdminAccountOperationsController;
use App\Http\Controllers\Admin\AgentController as AdminAgentController;
use App\Http\Controllers\Admin\BetController as AdminBetController;
use App\Http\Controllers\Admin\CashTransactionController as AdminCashController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\CommReportController as AdminCommReportController;
use App\Http\Controllers\Admin\CricketController as AdminCricketController;
use App\Http\Controllers\Admin\CricketDemoController as AdminCricketDemoController;
use App\Http\Controllers\Admin\CricketMarketController as AdminCricketMarketController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HierarchyController as AdminHierarchyController;
use App\Http\Controllers\Admin\LedgerController as AdminLedgerController;
use App\Http\Controllers\Admin\MarketController;
use App\Http\Controllers\Admin\MarketDraftController as AdminMarketDraftController;
use App\Http\Controllers\Admin\MatchController as AdminMatchController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchClientController as AdminSearchClientController;
use App\Http\Controllers\Admin\SecureCodeController as AdminSecureCodeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SportsController as AdminSportsController;
use App\Http\Controllers\Admin\StatementController as AdminStatementController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Agent\AccountController as AgentAccountController;
use App\Http\Controllers\Agent\BetController as AgentBetController;
use App\Http\Controllers\Agent\CommissionController as AgentCommissionController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\HierarchyController;
use App\Http\Controllers\Agent\PlayerController as AgentPlayerController;
use App\Http\Controllers\Agent\WalletController as AgentWalletController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\CricbuzzTestController;
use App\Http\Controllers\CricketLiveController;
use App\Http\Controllers\CricketMarketFeedController;
use App\Http\Controllers\CricketTestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoCricketController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\OddsController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public storefront
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sports', [SportController::class, 'index'])->name('sports.index');
Route::get('/sports/{sport:slug}', [SportController::class, 'index'])->name('sports.show');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
Route::get('/api/odds', [OddsController::class, 'bulk'])->name('api.odds');
Route::get('/api/cricket-markets', CricketMarketFeedController::class)->name('cricket.markets.feed');

// Isolated read-only Sportmonks Cricket API test page (no betting modules involved).
// Public read-only cricket section (Cricbuzz data, no betting involvement).
Route::get('/live-demo', [DemoCricketController::class, 'index'])->name('demo-cricket.index');
Route::get('/live-demo/{match}', [DemoCricketController::class, 'show'])->whereNumber('match')->name('demo-cricket.show');
Route::get('/cricket', [CricketLiveController::class, 'index'])->name('cricket.index');
Route::get('/cricket/board', [CricketLiveController::class, 'board'])->name('cricket.board');
Route::get('/cricket/{match}', [CricketLiveController::class, 'show'])->whereNumber('match')->name('cricket.show');

// Legacy / diagnostic test pages: admin-only so they cannot burn the API quota publicly.
Route::middleware(['auth', 'active', 'admin'])->group(function () {
    Route::get('/cricket-test', [CricketTestController::class, 'index'])->name('cricket-test.index');
    Route::get('/cricket-test/{fixture}', [CricketTestController::class, 'show'])->whereNumber('fixture')->name('cricket-test.show');

    Route::get('/cricbuzz-test', [CricbuzzTestController::class, 'index'])->name('cricbuzz-test.index');
    Route::get('/cricbuzz-results', [CricbuzzTestController::class, 'results'])->name('cricbuzz-test.results');
    Route::get('/cricbuzz-test/{match}', [CricbuzzTestController::class, 'show'])->whereNumber('match')->name('cricbuzz-test.show');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Client panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'client'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/my-bets', [BetController::class, 'index'])->name('bets.index');
    Route::get('/my-bets/{bet}', [BetController::class, 'show'])->name('bets.show');
    Route::post('/bets', [BetController::class, 'store'])->name('bets.store');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');

    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
});

/*
|--------------------------------------------------------------------------
| Agent panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/', [AgentDashboardController::class, 'index'])->name('dashboard');

    Route::get('matches', [App\Http\Controllers\Agent\MatchController::class, 'index'])->middleware('panel.module:matches')->name('matches.index');
    Route::get('matches/{match}', [App\Http\Controllers\Agent\MatchController::class, 'show'])->middleware('panel.module:matches')->name('matches.show');
    Route::post('matches/bet', [BetController::class, 'store'])->middleware('panel.module:matches')->name('bet.place');
    Route::get('my-bets', [AgentBetController::class, 'myBets'])->middleware('panel.module:mybets')->name('my-bets');

    Route::get('hierarchy', [HierarchyController::class, 'index'])->middleware('panel.module:network')->name('hierarchy.index');
    Route::get('hierarchy/create', [HierarchyController::class, 'create'])->middleware('panel.module:create')->name('hierarchy.create');
    Route::post('hierarchy', [HierarchyController::class, 'store'])->middleware('panel.module:create')->name('hierarchy.store');
    Route::get('hierarchy/{user}/edit', [HierarchyController::class, 'edit'])->middleware(['panel.module:network', 'panel.module:create'])->name('hierarchy.edit');
    Route::put('hierarchy/{user}', [HierarchyController::class, 'update'])->middleware(['panel.module:network', 'panel.module:create'])->name('hierarchy.update');

    Route::get('players', [AgentPlayerController::class, 'index'])->middleware('panel.module:players')->name('players.index');
    Route::get('players/create', [AgentPlayerController::class, 'create'])->middleware(['panel.module:players', 'panel.module:create'])->name('players.create');
    Route::post('players', [AgentPlayerController::class, 'store'])->middleware(['panel.module:players', 'panel.module:create'])->name('players.store');
    Route::get('players/{player}', [AgentPlayerController::class, 'show'])->middleware('panel.module:players')->name('players.show');

    Route::get('bets', [AgentBetController::class, 'index'])->middleware('panel.module:bets')->name('bets.index');
    Route::get('commissions', [AgentCommissionController::class, 'index'])->middleware('panel.module:commissions')->name('commissions.index');

    Route::get('wallet', [AgentWalletController::class, 'index'])->middleware('panel.module:wallet')->name('wallet.index');
    Route::post('wallet/withdraw', [AgentWalletController::class, 'withdraw'])->middleware('panel.module:wallet')->name('wallet.withdraw');

    Route::get('account', [AgentAccountController::class, 'index'])->middleware('panel.module:account')->name('account.index');
    Route::put('account/profile', [AgentAccountController::class, 'updateProfile'])->middleware('panel.module:account')->name('account.profile');
    Route::put('account/password', [AgentAccountController::class, 'updatePassword'])->middleware('panel.module:account')->name('account.password');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('access/{user}', [AdminAccessController::class, 'edit'])->name('access.edit');
    Route::post('access/{user}', [AdminAccessController::class, 'update'])->name('access.update');
    Route::post('access/{user}/reset', [AdminAccessController::class, 'reset'])->name('access.reset');

    Route::post('details/{level}/bulk-delete', [AdminHierarchyController::class, 'bulkDelete'])->name('hierarchy.bulk-delete');
    Route::post('details/{level}/bulk-update', [AdminHierarchyController::class, 'bulkUpdate'])->name('hierarchy.bulk-update');
    Route::get('details/{level}/limits', [AdminHierarchyController::class, 'limits'])->name('hierarchy.limits');
    Route::post('details/{level}/limits', [AdminHierarchyController::class, 'updateLimits'])->name('hierarchy.limits.update');
    Route::get('details/{level}/{user}/cash', [AdminHierarchyController::class, 'cashForm'])->name('hierarchy.cash');
    Route::post('details/{level}/{user}/cash', [AdminHierarchyController::class, 'cashStore'])->name('hierarchy.cash.store');
    Route::get('details/{level}/{user}/login-report', [AdminHierarchyController::class, 'loginReport'])->name('hierarchy.login-report');
    Route::get('details/{level}/{user}/statement', [AdminHierarchyController::class, 'statement'])->name('hierarchy.statement');
    Route::post('details/{level}/{user}/inactive', [AdminHierarchyController::class, 'toggleInactive'])->name('hierarchy.inactive');
    Route::post('details/{level}/{user}/block', [AdminHierarchyController::class, 'toggleBlock'])->name('hierarchy.block');
    Route::post('details/{level}/{user}/reset-password', [AdminHierarchyController::class, 'resetPassword'])->name('hierarchy.reset-password');
    Route::delete('details/{level}/{user}', [AdminHierarchyController::class, 'destroy'])->name('hierarchy.destroy');
    Route::get('details/{level}', [AdminHierarchyController::class, 'index'])->name('hierarchy.index');
    Route::get('details/{level}/create', [AdminHierarchyController::class, 'create'])->name('hierarchy.create');
    Route::post('details/{level}', [AdminHierarchyController::class, 'store'])->name('hierarchy.store');
    Route::get('details/{level}/{user}', [AdminHierarchyController::class, 'show'])->name('hierarchy.show');
    Route::get('details/{level}/{user}/edit', [AdminHierarchyController::class, 'edit'])->name('hierarchy.edit');
    Route::put('details/{level}/{user}', [AdminHierarchyController::class, 'update'])->name('hierarchy.update');

    Route::get('cricket', [AdminCricketController::class, 'index'])->name('cricket.index');
    Route::get('cricket/matches', [AdminCricketController::class, 'matches'])->name('cricket.matches');
    Route::post('cricket/matches/{match}/visibility', [AdminCricketController::class, 'toggleVisibility'])->name('cricket.visibility');
    Route::post('cricket/matches/{match}/refresh', [AdminCricketController::class, 'refresh'])->name('cricket.refresh');
    Route::get('cricket/health', [AdminCricketController::class, 'health'])->name('cricket.health');
    Route::get('cricket/settings', [AdminCricketController::class, 'settings'])->name('cricket.settings');
    Route::post('cricket/settings', [AdminCricketController::class, 'updateSettings'])->name('cricket.settings.update');
    Route::get('cricket/ai-markets', [AdminCricketMarketController::class, 'index'])->name('cricket.markets');
    Route::get('cricket/demo', [AdminCricketDemoController::class, 'index'])->name('cricket.demo');
    Route::post('cricket/demo', [AdminCricketDemoController::class, 'store'])->name('cricket.demo.store');
    Route::post('cricket/demo/{demoMatch}/start', [AdminCricketDemoController::class, 'start'])->name('cricket.demo.start');
    Route::post('cricket/demo/{demoMatch}/pause', [AdminCricketDemoController::class, 'pause'])->name('cricket.demo.pause');
    Route::post('cricket/demo/{demoMatch}/resume', [AdminCricketDemoController::class, 'resume'])->name('cricket.demo.resume');
    Route::post('cricket/demo/{demoMatch}/auto', [AdminCricketDemoController::class, 'auto'])->name('cricket.demo.auto');
    Route::post('cricket/demo/{demoMatch}/ball', [AdminCricketDemoController::class, 'ball'])->name('cricket.demo.ball');
    Route::post('cricket/ai-markets/settings', [AdminCricketMarketController::class, 'updateSettings'])->name('cricket.markets.settings');
    Route::post('cricket/ai-markets/run', [AdminCricketMarketController::class, 'run'])->name('cricket.markets.run');
    Route::post('cricket/ai-markets/matches/{match}/toggle', [AdminCricketMarketController::class, 'toggleMatch'])->name('cricket.markets.toggle');
    Route::post('cricket/ai-markets/candidates/{candidate}/approve', [AdminCricketMarketController::class, 'approve'])->name('cricket.markets.approve');
    Route::post('cricket/ai-markets/candidates/{candidate}/reject', [AdminCricketMarketController::class, 'reject'])->name('cricket.markets.reject');
    Route::post('cricket/ai-markets/rules/{rule}/suspend', [AdminCricketMarketController::class, 'suspend'])->name('cricket.markets.suspend');
    Route::post('cricket/ai-markets/rules/{rule}/reopen', [AdminCricketMarketController::class, 'reopen'])->name('cricket.markets.reopen');
    Route::post('cricket/ai-markets/rules/{rule}/void', [AdminCricketMarketController::class, 'void'])->name('cricket.markets.void');

    Route::get('sports-betting/inplay', [AdminSportsController::class, 'inplay'])->name('sports.inplay');
    Route::get('sports-betting/completed', [AdminSportsController::class, 'completed'])->name('sports.completed');
    Route::get('sports-betting/current-bets', [AdminSportsController::class, 'currentBets'])->name('sports.current-bets');
    Route::get('sports-betting/{match}/view', [AdminSportsController::class, 'matchView'])->name('sports.match-view');
    Route::get('sports-betting/{match}/pl-report', [AdminSportsController::class, 'matchPlReport'])->name('sports.match-pl-report');
    Route::get('sports-betting/{match}/pl', [AdminSportsController::class, 'matchPl'])->name('sports.match-pl');
    Route::get('sports-betting/{match}/limits', [AdminSportsController::class, 'matchLimits'])->name('sports.match-limits');
    Route::post('sports-betting/{match}/limits', [AdminSportsController::class, 'updateMatchLimit'])->name('sports.match-limits.update');
    Route::get('sports-betting/{match}/bets/{type}', [AdminSportsController::class, 'matchBets'])->name('sports.match-bets');
    Route::post('sports-betting/{match}/inplay', [AdminSportsController::class, 'toggleInplay'])->name('sports.toggle-inplay');

    Route::get('ledger/profit-loss', [AdminLedgerController::class, 'profitLoss'])->name('ledger.profit-loss');
    Route::get('ledger/{level}', [AdminLedgerController::class, 'show'])->name('ledger.show');

    Route::get('cash-transaction/{level}', [AdminCashController::class, 'index'])->name('cash.index');
    Route::post('cash-transaction/{level}/adjust', [AdminCashController::class, 'adjust'])->name('cash.adjust');
    Route::post('cash-transaction/{level}/store', [AdminCashController::class, 'store'])->name('cash.store');

    Route::get('comm-report', [AdminCommReportController::class, 'index'])->name('comm-report.index');

    Route::get('reports/secure-code', [AdminSecureCodeController::class, 'index'])->name('secure-code.index');
    Route::post('reports/secure-code/{user}/regenerate', [AdminSecureCodeController::class, 'regenerate'])->name('secure-code.regenerate');

    Route::resource('users', AdminUserController::class)->except(['destroy']);
    Route::resource('agents', AdminAgentController::class)->except(['destroy']);
    Route::post('agents/{agent}/payout', [AdminAgentController::class, 'payout'])->name('agents.payout');
    Route::get('commissions', [AdminCommissionController::class, 'index'])->name('commissions.index');
    Route::post('commissions/payout/{agent}', [AdminCommissionController::class, 'payout'])->name('commissions.payout');
    Route::post('users/{user}/balance', [AdminUserController::class, 'adjustBalance'])->name('users.balance');
    Route::post('users/{user}/toggle', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');
    Route::post('users/{user}/password', [AdminUserController::class, 'resetPassword'])->name('users.password');

    Route::resource('matches', AdminMatchController::class);
    Route::post('matches/{match}/result', [AdminMatchController::class, 'declareResult'])->name('matches.result');
    Route::post('matches/{match}/void', [AdminMatchController::class, 'voidMatch'])->name('matches.void');

    Route::post('matches/{match}/markets', [MarketController::class, 'store'])->name('markets.store');
    Route::patch('markets/{market}', [MarketController::class, 'update'])->name('markets.update');
    Route::delete('markets/{market}', [MarketController::class, 'destroy'])->name('markets.destroy');
    Route::post('markets/{market}/settle', [MarketController::class, 'settle'])->name('markets.settle');
    Route::post('markets/{market}/selections', [MarketController::class, 'storeSelection'])->name('selections.store');
    Route::patch('selections/{selection}', [MarketController::class, 'updateSelection'])->name('selections.update');
    Route::delete('selections/{selection}', [MarketController::class, 'destroySelection'])->name('selections.destroy');

    Route::get('drafts', [AdminMarketDraftController::class, 'index'])->name('drafts.index');
    Route::post('drafts', [AdminMarketDraftController::class, 'store'])->name('drafts.store');
    Route::put('drafts/{draft}', [AdminMarketDraftController::class, 'update'])->name('drafts.update');
    Route::post('drafts/{draft}/review', [AdminMarketDraftController::class, 'review'])->name('drafts.review');
    Route::delete('drafts/{draft}', [AdminMarketDraftController::class, 'destroy'])->name('drafts.destroy');

    Route::get('bets', [AdminBetController::class, 'index'])->name('bets.index');
    Route::get('bets/{bet}', [AdminBetController::class, 'show'])->name('bets.show');
    Route::put('bets/{bet}', [AdminBetController::class, 'update'])->name('bets.update');

    Route::get('payments/deposits', [PaymentController::class, 'deposits'])->name('payments.deposits');
    Route::post('payments/deposits/{deposit}/approve', [PaymentController::class, 'approveDeposit'])->name('payments.deposits.approve');
    Route::post('payments/deposits/{deposit}/reject', [PaymentController::class, 'rejectDeposit'])->name('payments.deposits.reject');

    Route::get('payments/withdrawals', [PaymentController::class, 'withdrawals'])->name('payments.withdrawals');
    Route::post('payments/withdrawals/{withdrawal}/approve', [PaymentController::class, 'approveWithdrawal'])->name('payments.withdrawals.approve');
    Route::post('payments/withdrawals/{withdrawal}/reject', [PaymentController::class, 'rejectWithdrawal'])->name('payments.withdrawals.reject');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('settings/platform', [SettingController::class, 'platform'])->name('settings.platform');
    Route::get('settings/appearance', [SettingController::class, 'appearance'])->name('settings.appearance');
    Route::post('settings/appearance', [SettingController::class, 'updateAppearance'])->name('settings.appearance.update');
    Route::post('settings/appearance/reset', [SettingController::class, 'resetAppearance'])->name('settings.appearance.reset');
    Route::get('settings/statement', [AdminStatementController::class, 'index'])->name('settings.statement');
    Route::delete('settings/statement/{transaction}', [AdminStatementController::class, 'destroy'])->name('settings.statement.destroy');
    Route::post('settings/statement/bulk-delete', [AdminStatementController::class, 'bulkDestroy'])->name('settings.statement.bulk-delete');
    Route::get('settings/account-operations', [AdminAccountOperationsController::class, 'index'])->name('settings.account-operations');
    Route::post('settings/account-operations', [AdminAccountOperationsController::class, 'adjust'])->name('settings.account-operations.adjust');
    Route::get('settings/casino-profit-loss', [SettingController::class, 'casinoProfitLoss'])->name('settings.casino-profit-loss');
    Route::get('settings/search-client', [AdminSearchClientController::class, 'index'])->name('settings.search-client');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/shares', [SettingController::class, 'updateShares'])->name('settings.shares');
});
