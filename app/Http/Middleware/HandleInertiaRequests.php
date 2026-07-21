<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user()?->loadMissing('committee');

        return [
            ...parent::share($request),
            'assets' => [
                'porpamnas' => '/assets/brand/logos/porpamnas/porpamnas-ix.png',
                'ptmb' => '/assets/brand/logos/ptmb/logo-ptmb-landscape.png',
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'committee' => $user->committee ? [
                        'id' => $user->committee->id,
                        'name' => $user->committee->name,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
