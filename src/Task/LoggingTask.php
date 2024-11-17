<?php

namespace adamcameron\php8\Task;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Spatie\Async\Task;

class LoggingTask extends Task
{
    private Logger $logger;

    public function __construct(private readonly string $message)
    {
    }

    public function configure()
    {
        $this->logger = new Logger("test-logger");
        $this->logger->pushHandler(new StreamHandler("/var/log/LoggingTest.log"));
    }

    public function run()
    {
        $this->logger->info($this->message);
    }
}
