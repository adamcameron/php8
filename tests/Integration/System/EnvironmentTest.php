<?php

namespace adamcameron\php8\tests\Integration\System;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Tests of environment variables")]
class EnvironmentTest extends TestCase
{
    #[TestDox("The expected environment variables exist")]
    #[DataProvider("provideCasesForEnvironmentVariablesTest")]
    public function testEnvironmentVariables(string $expectedEnvironmentVariable)
    {
        $this->assertNotFalse(
            getenv($expectedEnvironmentVariable),
            "Expected environment variable $expectedEnvironmentVariable to exist"
        );
    }

    public static function provideCasesForEnvironmentVariablesTest(): Generator
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
