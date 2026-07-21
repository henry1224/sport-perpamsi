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
        abort_unless($user && $user->isPdAdmin() && $user->isVerified() && $user->regional_committee_id, 403, 'Akun Pengurus Daerah belum terverifikasi.');

        return $next($request);
    }
}
