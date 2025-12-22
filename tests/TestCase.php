<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup untuk semua tests dengan disable CSRF middleware
     * karena testing tidak memerlukan CSRF token validation
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF middleware untuk semua tests
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }
}
