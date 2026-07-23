<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicErrorPageTest extends TestCase
{
    public function test_unknown_public_route_uses_stop_error_page(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page->component('Error')->where('status', 404));
    }
}
