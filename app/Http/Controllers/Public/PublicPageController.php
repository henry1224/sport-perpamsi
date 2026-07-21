<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Porpamnas\PublicDataService;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __construct(private readonly PublicDataService $data) {}

    public function home(): Response
    {
        return $this->render('Home');
    }

    public function agenda(): Response
    {
        return $this->render('Agenda');
    }

    public function seminar(): Response
    {
        return $this->render('Seminar');
    }

    public function hasil(): Response
    {
        return $this->render('Hasil');
    }

    public function cabor(): Response
    {
        return $this->render('Cabor');
    }

    public function bracket(): Response
    {
        return $this->render('Bracket');
    }

    public function ranking(): Response
    {
        return $this->render('Ranking');
    }

    public function venue(): Response
    {
        return $this->render('Venue');
    }

    public function peserta(): Response
    {
        return $this->render('Peserta');
    }

    private function render(string $page): Response
    {
        return Inertia::render($page, $this->data->pageProps());
    }
}
