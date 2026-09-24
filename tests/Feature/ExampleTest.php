<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_shows_the_landing_page_to_guests(): void
    {
        $this->get('/')->assertOk()->assertSee('DueTap');
    }
}
