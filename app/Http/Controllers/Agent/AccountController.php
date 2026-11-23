<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->user();

        $playerIds = $agent->downlinePlayerIds();

        $stats = [
            'players' => count($playerIds),
            'direct_players' => $agent->players()->count(),
            'turnover' => (float) \App\Models\Bet::whereIn('user_id', $playerIds)->sum('stake'),
            'pending_commission' => (float) $agent->agentCommissions()->where('status', 'pending')->sum('amount'),
            'paid_commission' => (float) $agent->agentCommissions()->where('status', 'paid')->sum('amount'),
        ];

        return view('agent.account', compact('agent', 'stats'));
    }

    public function updateProfile(Request $request)
    {
        $agent = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($agent->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
        ]);

        $agent->update($data);

        return back()->with('success', 'Agent profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $data['password']]);
        $request->session()->regenerate();

        OperationLog::record(
            'PASSWORD HAS BEEN CHANGED',
            'LAST PASSWORD ******, NEW PASSWORD IS ****** BY ['.$request->user()->agent_code.','.$request->user()->name.']',
            $request->user()
        );

        return back()->with('success', 'Password changed successfully.');
    }
}
