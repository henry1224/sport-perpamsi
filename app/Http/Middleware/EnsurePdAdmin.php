<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePdAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->isPdAdmin() && $user->regional_committee_id, 403, 'Bukan Pengurus Daerah.');

        return $next($request);
    }
}
