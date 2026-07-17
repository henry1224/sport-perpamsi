<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicHomeTest extends TestCase
{
    public function test_public_home_loads(): void
    {
        $this->get('/')->assertOk();
    }
}
