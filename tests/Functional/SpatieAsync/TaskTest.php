<?php

namespace adamcameron\php8\tests\Functional\SpatieAsync;

use adamcameron\php8\Task\SimpleTask;
use adamcameron\php8\Task\SlowDbCallTask;
use PHPUnit\Framework\TestCase;
use Spatie\Async\Pool;

/** @testdox tests of spatie/async Task-based functionality */
class TaskTest extends TestCase
{
    /** @testdox It can receive a Task object into the pool */
    public function testTask()
    {
        $pool = Pool::create();

        $startTime = microtime(true);
        for ($i = 1; $i <= 3; $i++) {
            $pool->add(new SlowDbCallTask($i, $startTime));
        }
        $poolPopulationTime = microtime(true) - $startTime;
        $this->assertLessThanOrEqual(1, $poolPopulationTime);

        $startTime = microtime(true);
        $results = $pool->wait();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(3, $executionTime);
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

    /** @testdox It supports a simplified version of a task */
    public function testSimpleTask()
    {
        $pool = Pool::create();

        $pool->add(new SimpleTask());

        $results = $pool->wait();

        $this->assertCount(1, $results);
        $this->assertEquals("G'day world from an async call", $results[0]);
    }
}
