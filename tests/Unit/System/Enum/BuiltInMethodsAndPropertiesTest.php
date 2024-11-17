<?php

namespace adamcameron\php8\tests\Unit\System\Enum;

use adamcameron\php8\tests\Unit\System\Fixtures\MaoriNumbers as MI;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Testing built-in methods of enums")]
class BuiltInMethodsAndPropertiesTest extends TestCase
{
    use CustomAssertionsTrait;

    #[TestDox("Its name can be returned")]
    public function testGetName()
    {
        $this->assertEquals("TORU", MI::TORU->name);
    }

    #[TestDox("Its value can be returned")]
    public function testGetValue()
    {
        $this->assertEquals(4, MI::WHĀ->value);
    }

    #[TestDox("It has a from method")]
    public function testFrom()
    {
        $this->assertEquals(MI::WHITU, MI::from(7));
    }

    #[TestDox("It has a from method that throws an exception")]
    public function testFromException()
    {
        $this->assertError(
            function () {
                MI::from(0);
            },
            sprintf("0 is not a valid backing value for enum %s", MI::CLASS)
        );
    }

    #[TestDox("It has a tryFrom method")]
    public function testTryFrom()
    {
        $this->assertEquals(MI::WARU, MI::tryFrom(8));
    }

    #[TestDox("It has a tryFrom method that returns null")]
    public function testTryFromNull()
    {
        $this->assertNull(MI::tryFrom(-1));
    }

    #[TestDox("It will type-coerce the argument for from and tryFrom")]
    public function testTryFromTypeCoercion()
    {
        $this->assertEquals(MI::IWA, MI::from("9"));
        $this->assertEquals(MI::TEKAU, MI::tryFrom(10.0));
    }

    #[TestDox("It can have consts, and supply the values for same")]
    public function testConsts()
    {
        $this->assertEquals(MI::THREE, MI::TORU);
    }

    #[TestDox("It is an object and has a class const")]
    public function testClassConst()
    {
        $this->assertEquals(MI::CLASS, get_class(MI::RIMA));
    }

    #[TestDox("It has a cases method which returns a listing")]
    public function testCases()
    {
        $someCases = array_slice(MI::cases(), 6, 4);
        $this->assertEquals(
            [MI::WHITU, MI::WARU, MI::IWA, MI::TEKAU],
            $someCases
        );
    }
}
