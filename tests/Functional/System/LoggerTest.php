<?php

namespace adamcameron\php8\tests\Functional\System;

use adamcameron\php8\Kernel;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Tests the logging config")]
class LoggerTest extends TestCase
{
    #[TestDox("It writes GetAddress entries to the expected log file")]
    public function testAddressServiceLogFile()
    {
        $kernel = new Kernel("test", false);
        $kernel->boot();
        $container = $kernel->getContainer();
        $logFile = $container->getParameter("kernel.logs_dir") . "/address_service.log";

        $logger = $container->get("monolog.logger.address_service");

        $this->assertEquals($logFile, $logger->getHandlers()[0]->getUrl());
    }
}
