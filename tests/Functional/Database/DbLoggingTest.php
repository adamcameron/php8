<?php

namespace adamcameron\php8\tests\Functional\Database;

use adamcameron\php8\tests\Integration\Fixtures\Database as DB;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Logging\Middleware;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Database tests")]
#[Group("slow")]
class DbLoggingTest extends TestCase
{
    #[TestDox("It can log SQL traffic")]
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
