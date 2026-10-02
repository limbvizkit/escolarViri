<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppTimezoneConfigTest extends TestCase
{
    public function test_application_timezone_is_configured_for_mexico_city(): void
    {
        $this->assertSame('America/Mexico_City', config('app.timezone'));
        $this->assertSame('America/Mexico_City', date_default_timezone_get());
    }
}
