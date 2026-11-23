<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isMasterPanelUser()) {
            abort(403, 'Agent access required.');
        }

        return $next($request);
    }
}
