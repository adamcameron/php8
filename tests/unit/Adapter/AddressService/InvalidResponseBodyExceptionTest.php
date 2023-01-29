<?php

namespace adamcameron\php8\tests\Adapter\AddressService;

use adamcameron\php8\Adapter\AddressService\InvalidResponseBodyException;
use PHPUnit\Framework\TestCase;

/** @testdox Tests of the InvalidResponseBodyException */
class InvalidResponseBodyExceptionTest extends TestCase
{
    /** @testdox it can take a custom exception message */
    public function testCanTakeCustomExceptionMessage()
    {
        $exception = new InvalidResponseBodyException("Custom message");
        $this->assertEquals("Custom message", $exception->getMessage());
    }

    /** @testdox it can take a default exception message */
    public function testCanTakeDefaultExceptionMessage()
    {
        $exception = new InvalidResponseBodyException();
        $this->assertEquals("Response JSON schema is not valid", $exception->getMessage());
    }
}
