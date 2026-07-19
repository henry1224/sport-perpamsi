<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Porpamnas\PublicDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoreController extends Controller
{
    public function __construct(private readonly PublicDataService $data) {}

    public function index(): Response
    {
        return Inertia::render('AdminScores', [
            'matches' => $this->data->adminScoreRows(),
            'audit' => $this->data->audit(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->data->updateScore($request->validate([
            'id' => ['required', 'string'],
            'sport' => ['required', 'string'],
            'team_a' => ['required', 'string'],
            'team_b' => ['required', 'string'],
            'score' => ['required', 'string', 'max:20', 'regex:/^\s*\d+\s*[-–—]\s*\d+\s*$/u'],
            'status' => ['required', 'in:scheduled,live,final,verified,disputed'],
            'venue' => ['nullable', 'string'],
            'time' => ['nullable', 'string'],
        ]));

        return back();
    }
}
