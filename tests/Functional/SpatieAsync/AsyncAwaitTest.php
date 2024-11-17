<?php

namespace adamcameron\php8\tests\Functional\SpatieAsync;

use adamcameron\php8\Task\LoggingTask;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Spatie\Async\Pool;

#[TestDox("Tests of async/await helper functions")]
class AsyncAwaitTest extends TestCase
{
    #[TestDox("It supports async/await")]
    public function testAsyncAwait()
    {
        $telemetry = [];

        $pool = Pool::create();
        for ($i = 1; $i <= 3; $i++) {
            $pool[] = async(function () use (&$telemetry, $i) {
                $telemetry[] = "NOT_ADDED_TO_TELEMETRY:$i";
                return "main:$i";
            })->then(function ($result) use (&$telemetry, $i) {
                $telemetry[] = $result;
                $telemetry[] = "then1:$i";
            })->then(function () use (&$telemetry, $i) {
                $telemetry[] = "then2:$i";
            });
        }
        await($pool);

        sort($telemetry);
        $this->assertEquals(
            [
                "main:1",
                "main:2",
                "main:3",
                "then1:1",
                "then1:2",
                "then1:3",
                "then2:1",
                "then2:2",
                "then2:3"
            ],
            $telemetry
        );
    }

    #[TestDox("It works with a task object")]
    public function testTaskObject()
    {
        $logFile = "/var/log/LoggingTest.log";
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $pool = Pool::create();
        for ($i = 1; $i <= 3; $i++) {
            $pool[] = async(new LoggingTask("main:$i"))
                ->then(new LoggingTask("then1:$i"))
                ->then(new LoggingTask("then2:$i"));
        }
        await($pool);

        $this->assertFileExists("/var/log/LoggingTest.log");
        $logContents = file_get_contents("/var/log/LoggingTest.log");
        $logContents = trim($logContents);
        $logRecords = explode("\n", $logContents);
        $this->assertCount(9, $logRecords); // this'll do for now
    }
}
