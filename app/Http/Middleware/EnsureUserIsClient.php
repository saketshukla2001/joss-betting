<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isClient()) {
            $target = $user->isAdmin() ? 'admin.dashboard' : 'agent.dashboard';

            return redirect()->route($target)->with('error', 'Use your own panel for this section.');
        }

        return $next($request);
    }
}
