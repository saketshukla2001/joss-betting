<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPanelModule($module)) {
            abort(403, 'This module is not enabled for your account.');
        }

        return $next($request);
    }
}
