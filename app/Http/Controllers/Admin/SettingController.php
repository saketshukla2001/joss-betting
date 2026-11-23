<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\DepositRequest;
use App\Models\MatchGame;
use App\Models\OperationLog;
use App\Models\Setting;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function platform()
    {
        $stats = [
            'users' => User::clients()->count(),
            'matches' => MatchGame::count(),
            'bets' => Bet::count(),
            'deposits' => DepositRequest::count(),
            'withdrawals' => WithdrawalRequest::count(),
        ];

        return view('admin.settings.platform', compact('stats'));
    }

    public function casinoProfitLoss()
    {
        return view('admin.settings.casino-profit-loss');
    }

    public function appearance()
    {
        return view('admin.settings.appearance');
    }

    public function updateAppearance(Request $request)
    {
        $data = $request->validate([
            'panel_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'panel_primary_dark' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'panel_primary_mid' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'panel_accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'panel_soft' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, strtolower($value));
        }
        Setting::flushCache();

        OperationLog::record('PANEL COLOR UPDATED', 'PANEL COLOR CHANGED TO '.strtoupper($data['panel_primary']).' BY ['.$request->user()->agent_code.','.$request->user()->name.']', $request->user());

        return back()->with('success', 'Panel colors updated. Refresh if your browser has cached the old colors.');
    }

    public function resetAppearance(Request $request)
    {
        foreach (['panel_primary', 'panel_primary_dark', 'panel_primary_mid', 'panel_accent', 'panel_soft'] as $key) {
            Setting::set($key, Setting::DEFAULTS[$key]);
        }
        Setting::flushCache();

        return back()->with('success', 'Panel colors reset to default green.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'currency' => ['required', 'string', 'max:5'],
            'min_stake' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'max_stake' => ['required', 'numeric', 'gt:min_stake', 'max:100000000'],
            'signup_bonus' => ['required', 'numeric', 'min:0', 'max:100000'],
            'min_deposit' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'min_withdrawal' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'support_email' => ['required', 'email', 'max:120'],
            'announcement' => ['nullable', 'string', 'max:500'],
            'maintenance_message' => ['nullable', 'string', 'max:300'],
            'odds_auto_move' => ['nullable', 'boolean'],
            'live_only_sport' => ['required', 'in:all,cricket'],
            'live_stale_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'rules' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ([
            'site_name', 'tagline', 'currency', 'min_stake', 'max_stake', 'signup_bonus',
            'min_deposit', 'min_withdrawal', 'support_email', 'announcement', 'maintenance_message', 'rules',
        ] as $key) {
            Setting::set($key, $data[$key] ?? '');
        }

        Setting::set('odds_auto_move', $request->boolean('odds_auto_move') ? '1' : '0');
        Setting::set('live_only_sport', $data['live_only_sport']);
        Setting::set('live_stale_hours', (string) (int) $data['live_stale_hours']);
        Setting::flushCache();

        return back()->with('success', 'Platform settings saved.');
    }

    public function updateShares(Request $request)
    {
        $data = $request->validate([
            'share_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'company_share_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'session_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $request->user()->update([
            'share_percent' => round((float) $data['share_percent'], 2),
            'company_share_percent' => round((float) $data['company_share_percent'], 2),
            'commission_rate' => round((float) $data['commission_rate'], 2),
            'session_commission_percent' => round((float) $data['session_commission_percent'], 2),
        ]);

        return back()->with('success', 'Share and commission terms updated.');
    }
}
