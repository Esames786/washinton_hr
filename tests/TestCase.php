<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** The only database tests may touch: a local copy with live's structure. */
    public const TEST_DATABASE = 'hello_devtest';

    protected function setUp(): void
    {
        parent::setUp();

        // .env points at the live shared database. Hard stop if the connection is anything but the
        // local test copy; phpunit.xml forces it, this makes sure it actually took effect.
        $db   = DB::connection()->getDatabaseName();
        $host = config('database.connections.mysql.host');
        if ($db !== self::TEST_DATABASE || ! in_array($host, ['127.0.0.1', 'localhost'], true)) {
            $this->fail("Refusing to run: tests must use the local '" . self::TEST_DATABASE . "' database (got '$db' on '$host').");
        }

        // No test may reach a real outside service (the CRM bridge included).
        Http::preventStrayRequests();
    }
}
