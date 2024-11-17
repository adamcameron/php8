<?php

namespace adamcameron\php8\Task;

use adamcameron\php8\tests\Integration\Fixtures\Database as DB;
use Doctrine\DBAL\Connection;
use Spatie\Async\Task;

class SlowDbCallTask extends Task
{
    private readonly Connection $connection;

    public function __construct(
        readonly private int $i,
        readonly private float $startTime
    ) {
    }

    public function configure(): void
    {
        $this->connection = DB::getDbalConnection();
    }

    public function run(): string
    {
        $result = $this->connection->executeQuery("CALL sleep_and_return(?)", [2]);
        return sprintf(
            "%d:%d:%d",
            $this->i,
            $result->fetchOne(),
            microtime(true) - $this->startTime
        );
    }
}
