<?php

namespace adamcameron\php8\tests\Functional\Database;

use adamcameron\php8\tests\Integration\Fixtures\Database as DB;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Logging\Middleware;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/** @testdox Tests Database objects */
class DbLoggingTest extends TestCase
{
    /** @testdox It can log SQL traffic */
    public function testLogging()
    {
        $testLogger = new Logger("test");
        $testHandler = new TestHandler();
        $testLogger->setHandlers([$testHandler]);
        $middleware = new Middleware($testLogger);

        $config = new Configuration();
        $config->setMiddlewares([$middleware]);

        $connection = DB::getDbalConnection($config);

        $result = $connection->executeQuery("SELECT * FROM numbers WHERE id = ?", [5]);

        $this->assertEquals([5, 'five', 'rima'], $result->fetchNumeric());

        $withSql = array_filter($testHandler->getRecords(), function ($record) {
            return in_array('SELECT * FROM numbers WHERE id = ?', $record->context);
        });
        $this->assertCount(1, $withSql);
    }
}
