<?php

namespace adamcameron\php8\tests\Unit;

use adamcameron\php8\Greeter;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Tests of the Greeter class")]
class GreeterTest extends TestCase
{
    #[TestDox("It greets formally by default")]
    public function testFormalGreeting()
    {
        $name = "Zachary";
        $expectedGreeting = "Hello, $name";
        $actualGreeting = Greeter::greet($name);
        $this->assertEquals(
            $expectedGreeting,
            $actualGreeting,
            "Expected greeting to be $expectedGreeting, but got $actualGreeting"
        );
    }

    #[TestDox("It greets informally")]
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
