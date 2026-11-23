<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'username' => [
                'required', 'string', 'min:4', 'max:20', 'alpha_dash',
                Rule::unique('users', 'username'),
                Rule::notIn(['admin', 'administrator', 'support', 'root', 'system']),
            ],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'You must accept the terms to create an account.',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => 'client',
                'status' => 'active',
                'last_login_at' => now(),
            ]);

            $bonus = (float) Setting::get('signup_bonus');

            if ($bonus > 0) {
                app(WalletService::class)->credit(
                    $user,
                    $bonus,
                    'bonus',
                    'Welcome bonus for joining '.Setting::get('site_name', 'BetZone')
                );
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to '.Setting::get('site_name', 'BetZone').'! Your account is ready.');
    }
}
