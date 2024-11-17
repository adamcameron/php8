<?php

namespace adamcameron\php8\tests\Unit\Shapes;

use adamcameron\php8\Shapes\Shape;
use PHPUnit\Framework\TestCase;

/** @testdox Tests Shape class */
class ShapeTest extends TestCase
{
    /** @testdox getColour returns the colour set by the constructor */
    public function testGetColour()
    {
        $green = "karariki";
        $shape = $this->getMockForAbstractClass(Shape::class, [$green]);

        $actualColour = $shape->getColour();

        $this->assertEquals($green, $actualColour);
    }

    /** @testdox getColour test can use the mock builder too */
    public function testGetColourUsingMockBuilder()
    {
        $blue = "kikorangi";
        $shape = $this
            ->getMockBuilder(Shape::class)
            ->setConstructorArgs([$blue])
            ->getMockForAbstractClass();

        $actualColour = $shape->getColour();

        $this->assertEquals($blue, $actualColour);
    }
}
