<?php

namespace adamcameron\php8\tests\Unit\System\Enum;

use adamcameron\php8\tests\Unit\System\Fixtures\MaoriNumbers as MI;
use PHPUnit\Framework\TestCase;

/** @testdox Testing built-in methods of enums */
class BuiltInMethodsAndPropertiesTest extends TestCase
{
    use CustomAssertionsTrait;

    /** @testdox Its name can be returned */
    public function testGetName()
    {
        $this->assertEquals("TORU", MI::TORU->name);
    }

    /** @testdox Its value can be returned */
    public function testGetValue()
    {
        $this->assertEquals(4, MI::WHĀ->value);
    }

    /** @testdox It has a from method */
    public function testFrom()
    {
        $this->assertEquals(MI::from(7), MI::WHITU);
    }

    /** @testdox It has a from method that throws an exception */
    public function testFromException()
    {
        $this->assertError(
            function () {
                MI::from(0);
            },
            sprintf("0 is not a valid backing value for enum %s", MI::CLASS)
        );
    }

    /** @testdox It has a tryFrom method */
    public function testTryFrom()
    {
        $this->assertEquals(MI::tryFrom(8), MI::WARU);
    }

    /** @testdox It has a tryFrom method that returns null */
    public function testTryFromNull()
    {
        $this->assertNull(MI::tryFrom(-1));
    }

    /** @testdox It will type-coerce the argument for from and tryFrom */
    public function testTryFromTypeCoercion()
    {
        $this->assertEquals(MI::from("9"), MI::IWA);
        $this->assertEquals(MI::tryFrom(10.0), MI::TEKAU);
    }

    /** @testdox It can have consts, and supply the values for same */
    public function testConsts()
    {
        $this->assertEquals(MI::THREE, MI::TORU);
    }

    /** @testdox It is an object and has a class const */
    public function testClassConst()
    {
        $this->assertEquals(get_class(MI::RIMA), MI::CLASS);
    }

    /** @testdox It has a cases method which returns a listing */
    public function testCases()
    {
        $someCases = array_slice(MI::cases(), 6, 4);
        $this->assertEquals(
            [MI::WHITU, MI::WARU, MI::IWA, MI::TEKAU],
            $someCases
        );
    }
}
