<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test root redirects to admin dashboard or shows admin login.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}
