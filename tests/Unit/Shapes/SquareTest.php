<?php

namespace adamcameron\php8\tests\Unit\Shapes;

use adamcameron\php8\Shapes\Square;
use PHPUnit\Framework\TestCase;

/** @testdox Tests Square class */
class SquareTest extends TestCase
{
    /** @testdox getColour returns the Square's colour */
    public function testGetColour()
    {
        $red = "whero";
        $square = new Square($red, 1);

        $actualColour = $square->getColour();

        $this->assertEquals($red, $actualColour);
    }

    /** @testdox getArea returns the Circle's area */
    public function testGetArea()
    {
        $square = new Square("NOT_TESTED", 2);

        $actualArea = $square->getArea();

        $this->assertEquals(4, $actualArea);
    }
}
