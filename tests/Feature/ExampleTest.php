<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_shows_cutcost_crm_pitch(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Cutcost');
        $response->assertSee('No marketplace');
        $response->assertDontSee('Featured shops');

    }
}
