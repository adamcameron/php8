<?php

namespace adamcameron\php8\tests\Unit\Shapes;

use adamcameron\php8\Shapes\Circle;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Tests Circle class")]
class CircleTest extends TestCase
{
    #[TestDox("getColour returns the Circle's colour")]
    public function testGetColour()
    {
        $orange = "karaka";
        $circle = new Circle($orange, 1);

        $actualColour = $circle->getColour();

        $this->assertEquals($orange, $actualColour);
    }

    #[TestDox("getArea returns the Circle's area")]
    public function testGetArea()
    {
        $circle = new Circle("NOT_TESTED", 2);

        $actualArea = $circle->getArea();

        $this->assertEquals(pi() * 4, $actualArea);
    }
}
