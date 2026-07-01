<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Laravel 13: CSRF middleware is PreventRequestForgery, not VerifyCsrfToken.
        // In tests we skip it globally — no session to hold a token.
        $this->withoutMiddleware(PreventRequestForgery::class);
    }
}
