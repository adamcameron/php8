<?php

namespace adamcameron\php8\tests\integration;

use PHPUnit\Framework\TestCase;

/** @testdox Tests of the PHP installation */
class PhpTest extends TestCase
{
    /** @testdox It has the expected PHP version */
    public function testPhpVersion()
    {
        $expectedPhpVersion = "8.2";
        $actualPhpVersion = phpversion();
        $this->assertStringStartsWith(
            $expectedPhpVersion,
            $actualPhpVersion,
            "Expected PHP version to start with $expectedPhpVersion, but got $actualPhpVersion"
        );
    }
}
