<?php

namespace adamcameron\php8\tests\Integration\System;

use \Generator;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    /**
     * @testdox The expected environment variables exist
     * @dataProvider expectedEnvironmentVariablesProvider
     */
    public function testEnvironmentVariables($expectedEnvironmentVariable)
    {
        $this->assertNotFalse(
            getenv($expectedEnvironmentVariable),
            "Expected environment variable $expectedEnvironmentVariable to exist"
        );
    }

    public function expectedEnvironmentVariablesProvider() : Generator
    {
        $varNames = [
            "MARIADB_HOST",
            "MARIADB_PORT",
            "MARIADB_USER",
            "MARIADB_DATABASE",
            "MARIADB_ROOT_PASSWORD",
            "MARIADB_PASSWORD",
            "ADDRESS_SERVICE_API_KEY"
        ];
        foreach ($varNames as $varName) {
            yield $varName => [$varName];
        }
    }
}
