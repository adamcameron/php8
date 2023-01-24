<?php

namespace adamcameron\php8\tests\integration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use \stdClass;

/** @testdox Tests the stub DB */
class DbTest extends TestCase
{

    /** @testdox it can fetch records from the test table */
    public function testFetchRecords()
    {
        $expectedRecords = [
            ["id" => 101, "value" => "Test row 1"],
            ["id" => 102, "value" => "Test row 2"]
        ];

        $connection = $this->getDbalConnection();
        $result = $connection->executeQuery("SELECT id, value FROM test ORDER BY id");

        $actualRecords = $result->fetchAllAssociative();

        $this->assertEquals($expectedRecords, $actualRecords);
    }

    private function getDbalConnection() : Connection
    {
        $parameters = $this->getConnectionParameters();
        return DriverManager::getConnection([
            'dbname' => $parameters->database,
            'user' => $parameters->username,
            'password' => $parameters->password,
            'host' => $parameters->host,
            'port' => $parameters->port,
            'driver' => 'pdo_mysql'
        ]);
    }

    private function getConnectionParameters() : stdClass
    {
        return (object) [
            "host" => "mariadb",
            "port" => "3306",
            "database" => getenv("MARIADB_DATABASE"),
            "username" => getenv("MARIADB_USER"),
            "password" => getenv("MARIADB_PASSWORD")
        ];
    }
}
