<?php

namespace adamcameron\php8\tests\Integration\System;

use adamcameron\php8\tests\Integration\Fixtures\Database as DB;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[TestDox("Database tests")]
#[Group("slow")]
class DbTest extends TestCase
{
    private const MARIADB_MAJOR_VERSION = 11;

    #[TestDox("It can connect to the Database using PDO")]
    public function testDbConnection()
    {
        $connection = DB::getPdoConnection();
        $result = $connection->query("SELECT @@VERSION");

        $this->assertStringStartsWith(
            self::MARIADB_MAJOR_VERSION,
            $result->fetchColumn()
        );
    }

    #[TestDox("It can connect to the Database using DBAL")]
    public function testDbalConnection()
    {
        $connection = DB::getDbalConnection();
        $result = $connection->executeQuery("SELECT @@VERSION");

        $this->assertStringStartsWith(
            self::MARIADB_MAJOR_VERSION,
            $result->fetchOne()
        );
    }

    #[TestDox("It can retrieve multiple records in one hit")]
    public function testMultipleRecords()
    {
        $connection = DB::getDbalConnection();
        $result = $connection->executeQuery("
            SELECT en, mi
            FROM numbers
            ORDER BY id
            LIMIT 2
        ");

        $oneAndTwo = $result->fetchAllAssociative();

        $expectedValues = [
            ['en' => 'one', 'mi' => 'tahi'],
            ['en' => 'two', 'mi' => 'rua']
        ];

        $this->assertEquals($expectedValues, $oneAndTwo);
    }

    #[TestDox("It will use a read-only connection if it can")]
    public function testPrimaryReadReplicaConnectionReadOnlyConnection()
    {
        $connection = DB::getPrimaryReadReplicaConnection();

        $result = $connection->executeQuery("SELECT @@VERSION");

        $this->assertStringStartsWith(
            self::MARIADB_MAJOR_VERSION,
            $result->fetchOne()
        );
        $this->assertFalse($connection->isConnectedToPrimary());
    }

    /** @SuppressWarnings(PHPMD.EmptyCatchBlock) */
    #[TestDox("It will rollback an update in a failed transaction")]
    public function testPrimaryReadReplicaConnectionRollback()
    {
        $testValue = "TEST_VALUE_" . uniqid();

        $connection = DB::getDbalConnection();
        try {
            $connection->transactional(
                function (Connection $connection) use ($testValue) {
                    $connection->executeStatement(
                        "INSERT INTO test (value) VALUES (:value)",
                        ["value" => $testValue]
                    );
                    $newId = $connection->lastInsertId();

                    $newRow = $connection->executeQuery(
                        "SELECT value FROM test WHERE id = ?",
                        [$newId]
                    );
                    $this->assertEquals($testValue, $newRow->fetchOne());

                    throw new RuntimeException("Force rollback");
                }
            );
        } catch (RuntimeException) {
        } finally {
            $rolledBackRow = $connection->executeQuery(
                "SELECT value FROM test WHERE value = ?",
                [$testValue]
            );
            $this->assertFalse($rolledBackRow->fetchOne());
        }
    }

    #[TestDox(
        "It will start a connection on a replica".
        "then change to the primary and stay there after a write operation"
    )]
    public function testPrimaryReadReplicaConnectionSwitchToPrimary()
    {
        $testValue = "TEST_VALUE_" . uniqid();

        $connection = DB::getPrimaryReadReplicaConnection();

        $this->assertFalse(
            $connection->isConnectedToPrimary(),
            "Should start on a replica"
        );
        $connection->beginTransaction();
        try {
            $this->assertTrue(
                $connection->isConnectedToPrimary(),
                "Should be on the primary in a transaction"
            );
            $connection->executeStatement(
                "INSERT INTO test (value) VALUES (:value)",
                ["value" => $testValue]
            );
            $newId = $connection->lastInsertId();

            $newRow = $connection->executeQuery(
                "SELECT value FROM test WHERE id = ?",
                [$newId]
            );
            $this->assertEquals($testValue, $newRow->fetchOne());
            $this->assertTrue(
                $connection->isConnectedToPrimary(),
                "Should still be on the primary after a read following a write"
            );
        } finally {
            $connection->rollBack();
        }
        $this->assertTrue(
            $connection->isConnectedToPrimary(),
            "Should still be on the primary after a rollback"
        );

        $connection = DB::getPrimaryReadReplicaConnection();
        $this->assertFalse(
            $connection->isConnectedToPrimary(),
            "A new connection should start on a replica"
        );
    }
}
