<?php

namespace System;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\DriverManager;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/** @testdox DB tests */
class DbTest extends TestCase
{
    private const MARIADB_MAJOR_VERSION = 10;

    /** @testdox it can connect to the DB using PDO */
    public function testDbConnection()
    {
        $connection = $this->getPdoConnection();
        $result = $connection->query("SELECT @@VERSION");

        $this->assertStringStartsWith(self::MARIADB_MAJOR_VERSION, $result->fetchColumn());
    }

    /** @testdox it can connect to the DB using DBAL */
    public function testDbalConnection()
    {
        $connection = $this->getDbalConnection();
        $result = $connection->executeQuery("SELECT @@VERSION");

        $this->assertStringStartsWith(self::MARIADB_MAJOR_VERSION, $result->fetchOne());
    }
    /** @testdox it can retrieve multiple records in one hit */
    public function testMultipleRecords()
    {
        $connection = $this->getDbalConnection();
        $result = $connection->executeQuery("SELECT en, mi FROM numbers ORDER BY id LIMIT 2");

        $oneAndTwo = $result->fetchAllAssociative();

        $expectedValues = [
            ['en' => 'one', 'mi' => 'tahi'],
            ['en' => 'two', 'mi' => 'rua']
        ];

        $this->assertEquals($expectedValues, $oneAndTwo);
    }

    /** @testdox it will use a read-only connection if it can */
    public function testPrimaryReadReplicaConnectionReadOnlyConnection()
    {
        $connection = $this->getPrimaryReadReplicaConnection();

        $result = $connection->executeQuery("SELECT @@VERSION");

        $this->assertStringStartsWith(self::MARIADB_MAJOR_VERSION, $result->fetchOne());
        $this->assertFalse($connection->isConnectedToPrimary());
    }

    /**
     * @testdox it will rollback an update in a failed transaction
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function testPrimaryReadReplicaConnectionRollback()
    {
        $testValue = "TEST_VALUE_" . uniqid();

        $connection = $this->getDbalConnection();
        try {
            $connection->transactional(function (Connection $connection) use ($testValue) {
                $connection->executeStatement(
                    "INSERT INTO test (value) VALUES (:value)",
                    ["value" => $testValue]
                );
                $newId = $connection->lastInsertId();

                $newRow = $connection->executeQuery("SELECT value FROM test WHERE id = ?", [$newId]);
                $this->assertEquals($testValue, $newRow->fetchOne());

                throw new RuntimeException("Force rollback");
            });
        } catch (RuntimeException $ingored) {
        } finally {
            $rolledBackRow = $connection->executeQuery("SELECT value FROM test WHERE value = ?", [$testValue]);
            $this->assertFalse($rolledBackRow->fetchOne());
        }
    }

    /** @testdox a connection will start on a replica then change to the primary and stay there after a write operation */
    public function testPrimaryReadReplicaConnectionSwitchToPrimary()
    {
        $testValue = "TEST_VALUE_" . uniqid();

        $connection = $this->getPrimaryReadReplicaConnection();

        $this->assertFalse($connection->isConnectedToPrimary(), "Should start on a replica");
        $connection->beginTransaction();
        try {
            $this->assertTrue($connection->isConnectedToPrimary(), "Should be on the primary in a transaction");
            $connection->executeStatement(
                "INSERT INTO test (value) VALUES (:value)",
                ["value" => $testValue]
            );
            $newId = $connection->lastInsertId();

            $newRow = $connection->executeQuery("SELECT value FROM test WHERE id = ?", [$newId]);
            $this->assertEquals($testValue, $newRow->fetchOne());
            $this->assertTrue(
                $connection->isConnectedToPrimary(),
                "Should still be on the primary after a read following a write"
            );
        } finally {
            $connection->rollBack();
        }
        $this->assertTrue($connection->isConnectedToPrimary(), "Should still be on the primary after a rollback");

        $connection = $this->getPrimaryReadReplicaConnection();
        $this->assertFalse($connection->isConnectedToPrimary(), "A new connection should start on a replica");
    }

    private function getConnectionParameters() : stdClass
    {
        return (object) [
            "host" => getenv("MARIADB_HOST"),
            "port" => getenv("MARIADB_PORT"),
            "database" => getenv("MARIADB_DATABASE"),
            "username" => getenv("MARIADB_USER"),
            "password" => getenv("MARIADB_PASSWORD")
        ];
    }

    private function getPdoConnection() : PDO
    {
        $parameters = $this->getConnectionParameters();

        return new PDO(
            "mysql:"
            . "host=" . $parameters->host
            . ";port=" . $parameters->port
            . ";dbname=" . $parameters->database,
            $parameters->username,
            $parameters->password
        );
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

    private function getPrimaryReadReplicaConnection() : PrimaryReadReplicaConnection
    {
        $parameters = $this->getConnectionParameters();
        return DriverManager::getConnection([
            'wrapperClass' => PrimaryReadReplicaConnection::class,
            'driver' => 'pdo_mysql',
            'primary' => [
                'host' => $parameters->host,
                'port' => $parameters->port,
                'user' => $parameters->username,
                'password' => $parameters->password,
                'dbname' => $parameters->database
            ],
            'replica' => [[
                'host' => $parameters->host,
                'port' => $parameters->port,
                'user' => $parameters->username,
                'password' => $parameters->password,
                'dbname' => $parameters->database
            ]]
        ]);
    }
}
