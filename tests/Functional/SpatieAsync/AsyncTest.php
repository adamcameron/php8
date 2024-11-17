<?php

namespace adamcameron\php8\tests\Functional\SpatieAsync;

use adamcameron\php8\tests\Integration\Fixtures\Database as DB;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Spatie\Async\Pool;

#[TestDox("Tests of spatie/async async functionality")]
#[Group("slow")]
class AsyncTest extends TestCase
{
    #[TestDox("It can call a slow proc multiple times async")]
    public function testSlowProcAsync()
    {
        $connection = DB::getDbalConnection();

        $pool = Pool::create();

        $startTime = microtime(true);
        for ($i = 1; $i <= 3; $i++) {
            $pool->add(function () use ($connection, $i, $startTime) {
                $result = $connection->executeQuery(
                    "CALL sleep_and_return(?)",
                    [2]
                );
                return sprintf(
                    "%d:%d:%d",
                    $i,
                    $result->fetchOne(),
                    microtime(true) - $startTime
                );
            });
        }
        $poolPopulationTime = microtime(true) - $startTime;
        $this->assertLessThanOrEqual(1, $poolPopulationTime);

        $startTime = microtime(true);
        $results = $pool->wait();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(
            4,
            $executionTime,
            "Should execute in less time than it takes to run two sync calls"
        );
        $this->assertCount(3, $results);

        $resultAsString = implode(",", $results);
        $expectedResults = [
            "/1:2:[2-3]/",
            "/2:2:[2-3]/",
            "/3:2:[2-3]/"
        ];
        array_walk($expectedResults, function ($expectedResult) use ($resultAsString) {
            $this->assertMatchesRegularExpression(
                $expectedResult,
                $resultAsString,
                "$expectedResult not found in $resultAsString"
            );
        });
    }

    #[TestDox("It uses a then handler which acts on the result")]
    public function testSlowProcAsyncThen()
    {
        $connection = DB::getDbalConnection();

        $pool = Pool::create();

        $metrics = [];
        $startTime = microtime(true);
        for ($i = 1; $i <= 3; $i++) {
            $pool
                ->add(function () use ($connection) {
                    $result = $connection->executeQuery(
                        "CALL sleep_and_return(?)",
                        [2]
                    );
                    return $result->fetchOne();
                })
                ->then(function ($result) use (&$metrics, $i, $startTime) {
                    $metrics[] = sprintf(
                        "%d:%d:%d",
                        $i,
                        $result,
                        microtime(true) - $startTime
                    );
                });
        }

        $startTime = microtime(true);
        $results = $pool->wait();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(
            4,
            $executionTime,
            "Should execute in less time than it takes to run two sync calls"
        );
        $this->assertEquals([2, 2, 2], $results);
        $this->assertCount(3, $metrics);
        $this->assertContains("1:2:2", $metrics, "1:2:2 not found in " . implode(",", $metrics));
        $this->assertContains("2:2:2", $metrics, "2:2:2 not found in " . implode(",", $metrics));
        $this->assertContains("3:2:2", $metrics, "3:2:2 not found in " . implode(",", $metrics));
    }

    #[TestDox("It supports a timeout")]
    public function testSlowProcAsyncTimeout()
    {
        $connection = DB::getDbalConnection();

        $pool = Pool::create();
        $pool->timeout(1);

        $timeOuts = [];

        $startTime = microtime(true);
        for ($i = 1; $i <= 3; $i++) {
            $pool
                ->add(function () use ($connection) {
                    $result = $connection->executeQuery("CALL sleep_and_return(?)", [2]);
                    return $result->fetchOne();
                })
                ->timeout(function () use (&$timeOuts, $i, $startTime) {
                    $timeOuts[] = sprintf(
                        "TIMED OUT ON ITERATION %d after %d seconds",
                        $i,
                        microtime(true) - $startTime
                    );
                    return false;
                });
        }

        $results = $pool->wait();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertEquals(
            [
                "TIMED OUT ON ITERATION 1 after 1 seconds",
                "TIMED OUT ON ITERATION 2 after 1 seconds",
                "TIMED OUT ON ITERATION 3 after 1 seconds"
            ],
            $timeOuts
        );

        $this->assertLessThan(2, $executionTime);

        $this->assertEquals([], $results);
    }

    #[TestDox("It can stop a pool")]
    public function testPoolStop()
    {
        $pool = Pool::create();

        for ($i = 0; $i < 10000; $i++) {
            $pool->add(function () {
                return rand(0, 100);
            })->then(function ($output) use ($pool) {
                // If one of them randomly picks 100, end the pool early.
                if ($output === 100) {
                    $pool->stop();
                }
            });
        }

        $results = $pool->wait();

        $this->assertLessThan(10000, count($results));
        $this->assertContains(100, $results);
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) $_ on the catch is required but not needed */
    #[TestDox("It can stop a pool from a catch")]
    public function testPoolStopInCatch()
    {
        $pool = Pool::create();

        for ($i = 0; $i < 10000; $i++) {
            $pool->add(function () {
                $result = rand(0, 100);
                if ($result === 100) {
                    throw new Exception("Something went wrong");
                }
                return $result;
            })->catch(function (Exception $_) use ($pool) {
                $pool->stop();
            });
        }

        $results = $pool->wait();

        $this->assertLessThan(10000, count($results));
        $this->assertNotContains(100, $results);
    }
}
