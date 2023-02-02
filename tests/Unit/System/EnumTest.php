<?php

namespace adamcameron\php8\tests\Unit\System;

use adamcameron\php8\tests\Unit\System\Fixtures\MaoriNumbers as MI;
use PHPUnit\Framework\TestCase;

/** @testdox Testing enums */
class EnumTest extends TestCase
{

    /** @testdox It can have static methods */
    public function testStaticMethods()
    {
        $this->assertEquals("one", MI::asEnglish(1));
    }

    /** @testdox It can have instance methods */
    public function testInstanceMethods()
    {
        $this->assertEquals("two", MI::RUA->toEnglish());
    }

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

    /** @testdox It encodes to JSON OK */
    public function testJsonEncode()
    {
        $this->assertEquals('{"rima":5}', json_encode(["rima" => MI::RIMA]));
    }

    /** @testdox It cannot be type-coerced */
    public function testTypeCoercion()
    {
        $this->expectError(); // NB: not an exception; an error
        $this->expectErrorMessageMatches("/.*MaoriNumbers could not be converted to int.*/");
        $this->assertEquals(sprintf("ono: %d", MI::ONO), "ono: 6");
    }

    /** @testdox It has a from method */
    public function testFrom()
    {
        $this->assertEquals(MI::from(7), MI::WHITU);
    }

    /** @testdox It has a from method that throws an exception */
    public function testFromException()
    {
        $this->expectError(\ValueError ::class);
        $this->expectErrorMessage();
        MI::from(0);
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

    /** @testdox It can be used in a match expression */
    public function testMatch()
    {
        $this->assertEquals("odd", MI::TAHI->getParity());
        $this->assertEquals("even", MI::RUA->getParity());
    }

    /** @testdox It can have consts, and supply the values for same */
    public function testConsts()
    {
        $this->assertEquals(MI::THREE, MI::TORU);
    }

    /** @testdox It can use traits */
    public function testTraits()
    {
        $this->assertEquals(MI::FOUR, MI::WHĀ);
    }

    /** @testdox It is an object and has a class const */
    public function testClassConst()
    {
        $this->assertEquals(get_class(MI::RIMA), MI::CLASS);
    }

    /** @testdox It supports the __invoke magic method */
    public function testInvoke()
    {
        $this->assertEquals("six", (MI::ONO)());
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

    /** @testdox It can be serialised */
    public function testSerialize()
    {
        $this->assertMatchesRegularExpression(
            sprintf('/^E:\d+:"%s:%s";$/', preg_quote(MI::TAHI::CLASS), MI::TAHI->name ),
            serialize(MI::TAHI)
        );
    }

}
