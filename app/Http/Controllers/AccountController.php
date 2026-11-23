<?php

namespace App\Http\Controllers;

use App\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'bets' => $user->bets()->count(),
            'won' => $user->bets()->where('status', 'won')->count(),
            'staked' => (float) $user->bets()->sum('stake'),
            'returns' => (float) $user->bets()->where('status', 'won')->sum('payout'),
        ];

        return view('account.index', compact('user', 'stats'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
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
