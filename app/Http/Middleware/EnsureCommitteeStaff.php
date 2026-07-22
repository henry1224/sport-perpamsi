<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommitteeStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isVerified() && in_array($request->user()->role, ['scorekeeper', 'sport_coordinator'], true), 403);

        return $next($request);
    }
}
