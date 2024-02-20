<?php

namespace adamcameron\php8\tests\Unit;

use adamcameron\php8\Greeter;

use PHPUnit\Framework\TestCase;

/** @testdox Tests of the Greeter class */
class GreeterTest extends TestCase
{
    /** @testdox It greets formally */
    public function testFormalGreeting()
    {
        $name = "Zachary";
        $expectedGreeting = "Hello, $name";
        $actualGreeting = Greeter::greet($name, Greeter::FORMAL);
        $this->assertEquals(
            $expectedGreeting,
            $actualGreeting,
            "Expected greeting to be $expectedGreeting, but got $actualGreeting"
        );
    }

    /** @testdox It greets informally */
    public function testInformalGreeting()
    {
        $this->markTestSkipped("skipping this so the coverage report is more interesting");
        $name = "Zachary";
        $expectedGreeting = "Hi, $name";
        $actualGreeting = Greeter::greet($name, Greeter::INFORMAL);
        $this->assertEquals(
            $expectedGreeting,
            $actualGreeting,
            "Expected greeting to be $expectedGreeting, but got $actualGreeting"
        );
    }
}
