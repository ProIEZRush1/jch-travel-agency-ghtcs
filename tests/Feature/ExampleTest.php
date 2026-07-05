<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        // Root '/' is the public JCH Travel Agency landing page.
        $this->get('/')->assertOk();
    }
}
