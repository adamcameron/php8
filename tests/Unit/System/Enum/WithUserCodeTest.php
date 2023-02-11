<?php

namespace adamcameron\php8\tests\Unit\System\Enum;

use adamcameron\php8\tests\Unit\System\Fixtures\MaoriNumbers as MI;
use PHPUnit\Framework\TestCase;

/** @testdox Testing use of enums */
class WithUserCodeTest extends TestCase
{
    use CustomAssertionsTrait;

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

    /** @testdox It encodes to JSON OK */
    public function testJsonEncode()
    {
        $this->assertEquals('{"rima":5}', json_encode(["rima" => MI::RIMA]));
    }

    /** @testdox It *cannot* be type-coerced */
    public function testTypeCoercion()
    {
        // NB: `1` seems to be the result of coercing an enum to an int
        // also emits a warning, which PHPUNit is being unhelpful about,so suppressing
        @$this->assertEquals(1, (int)MI::ONO);
    }

    /** @testdox It can be used in a match expression */
    public function testMatch()
    {
        $this->assertEquals("odd", MI::TAHI->getParity());
        $this->assertEquals("even", MI::RUA->getParity());
    }

    /** @testdox It can use traits */
    public function testTraits()
    {
        $this->assertEquals(MI::FOUR, MI::WHĀ);
    }

    /** @testdox It supports the __invoke magic method */
    public function testInvoke()
    {
        $this->assertEquals("six", (MI::ONO)());
    }

    /** @testdox It can be serialised */
    public function testSerialize()
    {
        $this->assertMatchesRegularExpression(
            sprintf('/^E:\d+:"%s:%s";$/', preg_quote(MI::TAHI::CLASS), MI::TAHI->name),
            serialize(MI::TAHI)
        );
    }
}
