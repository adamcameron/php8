<?php

namespace adamcameron\php8\tests\Functional\Async;

use adamcameron\php8\tests\Integration\Fixtures\Database as DB;
use PHPUnit\Framework\TestCase;
use Spatie\Async\Pool;
use \Exception;

/** @testdox tests of spatie/async (https://github.com/spatie/async) */
class SpatieAsyncTest extends TestCase
{
    /** @testdox It can call a slow proc multiple times async */
    public function testSlowProcAsync()
    {
        $connection = DB::getDbalConnection();

        $pool = Pool::create();

        $startTime = microtime(true);
        for ($i = 1; $i <= 3; $i++) {
            $pool->add(function () use ($connection, $i, $startTime) {
                $result = $connection->executeQuery("CALL sleep_and_return(?)", [2]);
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

        $this->assertLessThan(3, $executionTime);
        $this->assertCount(3, $results);
        $this->assertContains("1:2:2", $results, "1:2:2 not found in " . implode(",", $results));
        $this->assertContains("2:2:2", $results, "2:2:2 not found in " . implode(",", $results));
        $this->assertContains("3:2:2", $results, "3:2:2 not found in " . implode(",", $results));
    }

    /** @testdox It uses a then handler which acts on the result */
    public function testSlowProcAsyncThen()
    {
        $connection = DB::getDbalConnection();

        $pool = Pool::create();

        $metrics = [];
        $startTime = microtime(true);
        for ($i = 1; $i <= 3; $i++) {
            $pool
                ->add(function () use ($connection) {
                    $result = $connection->executeQuery("CALL sleep_and_return(?)", [2]);
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

        $this->assertLessThan(3, $executionTime);
        $this->assertEquals([2, 2, 2], $results);
        $this->assertCount(3, $metrics);
        $this->assertContains("1:2:2", $metrics, "1:2:2 not found in " . implode(",", $metrics));
        $this->assertContains("2:2:2", $metrics, "2:2:2 not found in " . implode(",", $metrics));
        $this->assertContains("3:2:2", $metrics, "3:2:2 not found in " . implode(",", $metrics));
    }

    /** @testdox It supports a timeout */
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

    /** @testdox it supports exception handling */
    public function testAsyncException()
    {
        $pool = Pool::create();
        $pool
            ->add(function () {
                throw new \Exception("This is an exception");
            })
            ->catch(function (\Exception $exception) {
                $this->assertStringStartsWith("This is an exception", $exception->getMessage());
            });

        $pool->wait();
    }

    /** @testdox It does not support exception handling from a then handler */
    public function testAsyncExceptionFromThen()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("This is an exception");

        $pool = Pool::create();
        $pool
            ->add(function () {
                // do nothing
            })
            ->then(function () {
                throw new \Exception("This is an exception");
            })
            ->catch(function (\Exception $exception) {
                $this->assertStringStartsWith("This is an exception", $exception->getMessage());
            });

        $pool->wait();
    }

    /**  @testdox It can stop a pool */
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

    /**
     * @testdox It can stop a pool from a catch
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) $_ on the catch is required but not needed
     */
    public function testPoolStopInCatch()
    {
        $pool = Pool::create();

        for ($i = 0; $i < 10000; $i++) {
            $pool->add(function () {
                $result = rand(0, 100);
                if ($result === 100) {
                    throw new \Exception("Something went wrong");
                }
                return $result;
            })->catch(function (\Exception $_) use ($pool) {
                $pool->stop();
            });
        }

        $results = $pool->wait();

        $this->assertLessThan(10000, count($results));
        $this->assertNotContains(100, $results);
    }
}
