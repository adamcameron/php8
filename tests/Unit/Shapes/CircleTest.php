<?php

namespace adamcameron\php8\tests\Unit\Shapes;

use adamcameron\php8\Shapes\Circle;
use PHPUnit\Framework\TestCase;

/** @testdox Tests Circle class */
class CircleTest extends TestCase
{
    /** @testdox getColour returns the Circle's colour */
    public function testGetColour()
    {
        $orange = "karaka";
        $circle = new Circle($orange, 1);

        $actualColour = $circle->getColour();

        $this->assertEquals($orange, $actualColour);
    }

    /** @testdox getArea returns the Circle's area */
    public function testGetArea()
    {
        $circle = new Circle("NOT_TESTED", 2);

        $actualArea = $circle->getArea();

        $this->assertEquals(pi() * 4, $actualArea);
    }
}
